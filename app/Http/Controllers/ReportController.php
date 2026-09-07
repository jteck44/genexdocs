<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\ReportType;
use App\Services\ReportGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct(private ReportGeneratorService $generator)
    {
    }

    /**
     * Liste de tous les rapports visibles par l'utilisateur, avec
    * recherche par titre et filtre "sans mandant" pour repérer les
     * anciens dossiers orphelins.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->query('q');
        $onlyOrphans = $request->boolean('sans_mandant');

        $reports = Report::with(['type', 'mandant'])
            ->when(! $user->isDirector(), fn ($q) => $q->where('author_id', $user->id))
            ->when($search, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($onlyOrphans, fn ($q) => $q->whereNull('mandant_id'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('reports.index', compact('reports', 'search', 'onlyOrphans'));
    }

    public function create(string $typeSlug)
    {
        $type = ReportType::where('slug', $typeSlug)->firstOrFail();

        return view('reports.create', compact('type'));
    }

    public function store(Request $request, string $typeSlug)
    {
        $type = ReportType::where('slug', $typeSlug)->firstOrFail();

        $rules = ['title' => 'required|string|max:255'];
        foreach ($type->fields as $field) {
            $rules["data.{$field['key']}"] = $field['type'] === 'table' ? 'nullable|array' : 'nullable|string';
        }
        $validated = $request->validate($rules);

        if ($request->filled('mandant_id')) {
            $mandantId = $request->input('mandant_id');
        } elseif ($request->filled('new_mandant_name')) {
            $mandantId = \App\Models\Mandant::create(['name' => $request->input('new_mandant_name')])->id;
        } else {
            $mandantId = null;
        }

        $report = Report::create([
            'report_type_id' => $type->id,
            'mandant_id' => $mandantId,
            'author_id' => Auth::id(),
            'title' => $validated['title'],
            'data' => $validated['data'] ?? [],
            'status' => 'draft',
        ]);

        return redirect()
            ->route('reports.show', $report->id)
            ->with('status', 'Rapport créé en brouillon.');
    }

    public function show(Report $report)
    {
        $this->authorizeView($report);

        return view('reports.show', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        $this->authorizeEdit($report);

        $rules = ['title' => 'required|string|max:255'];
        foreach ($report->type->fields as $field) {
            $rules["data.{$field['key']}"] = $field['type'] === 'table' ? 'nullable|array' : 'nullable|string';
        }
        $validated = $request->validate($rules);

        $report->update([
            'title' => $validated['title'],
            'data' => $validated['data'] ?? [],
        ]);

        return back()->with('status', 'Rapport mis à jour.');
    }

    public function exportDraft(Report $report)
    {
        $this->authorizeEdit($report);

        $relativePath = $this->generator->generateDraft($report);

        return Response::download(
            Storage::path($relativePath),
            \Illuminate\Support\Str::slug($report->title).'-brouillon.docx'
        );
    }

    public function submit(Report $report)
    {
        $this->authorizeEdit($report);

        $this->logStatusChange($report, $report->status, 'submitted');

        $report->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('reports.show', $report->id)
            ->with('status', 'Rapport soumis au directeur.');
    }

    public function reject(Request $request, Report $report)
    {
        $this->authorizeDirectorAction($report);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $this->logStatusChange($report, $report->status, 'rejected', $validated['rejection_reason']);

        $report->update([
            'status' => 'rejected',
            'director_id' => Auth::id(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()
            ->route('reports.show', $report->id)
            ->with('status', 'Rapport renvoyé à l’expert pour correction.');
    }

    public function validateReport(Report $report)
    {
        $this->authorizeDirectorAction($report);

        $logoPath = config('genexdocs.logo_path');

        if (! File::exists($logoPath)) {
            return back()->withErrors([
                'logo' => "Le fichier logo est introuvable à l'emplacement configuré ({$logoPath}).",
            ]);
        }

        $this->generator->generateValidated($report, $logoPath);

        $this->logStatusChange($report, $report->status, 'validated');

        $report->update([
            'status' => 'validated',
            'director_id' => Auth::id(),
            'validated_at' => now(),
        ]);

        return redirect()
            ->route('reports.show', $report->id)
            ->with('status', 'Rapport validé et document officiel généré.');
    }

    public function downloadValidated(Report $report)
    {
        $this->authorizeView($report);

        if (! $report->isValidated() || ! $report->validated_path) {
            abort(404, "Ce rapport n'a pas encore de document officiel validé.");
        }

        return Response::download(
            Storage::path($report->validated_path),
            \Illuminate\Support\Str::slug($report->title).'-final.docx'
        );
    }

    /**
     * Suppression d'un brouillon par son propre auteur.
     */
    public function destroy(Report $report)
    {
        if ($report->author_id !== Auth::id()) {
            abort(403);
        }

        if (! $report->isDraft()) {
            abort(403, "Seuls les brouillons peuvent être supprimés.");
        }

        $report->delete();

        return redirect()->route('dashboard')->with('status', 'Brouillon supprimé.');
    }

    /**
     * Suppression réservée au directeur, pour nettoyer les vieux
    * rapports jamais rattachés à un mandant.
     */

    public function destroyAsDirector(Report $report)
    {
        if (! Auth::user()->isDirector()) {
            abort(403);
        }

        $report->delete();

        return back()->with('status', "Rapport « {$report->title} » supprimé.");
    }
    // ------------------------------------------------------------------
    // Méthodes privées
    // ------------------------------------------------------------------

    private function logStatusChange(Report $report, string $from, string $to, ?string $reason = null): void
    {
        ReportStatusLog::create([
            'report_id' => $report->id,
            'user_id' => Auth::id(),
            'from_status' => $from,
            'to_status' => $to,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }

    private function authorizeView(Report $report): void
    {
        $user = Auth::user();

        if ($user->isDirector() || $report->author_id === $user->id) {
            return;
        }

        abort(403);
    }

    private function authorizeEdit(Report $report): void
    {
        $user = Auth::user();

        if ($report->author_id !== $user->id) {
            abort(403, "Seul l'auteur du rapport peut le modifier.");
        }

        if (! $report->isEditableByAuthor()) {
            abort(403, "Ce rapport n'est plus modifiable dans son statut actuel ({$report->status}).");
        }
    }

    private function authorizeDirectorAction(Report $report): void
    {
        if (! Auth::user()->isDirector()) {
            abort(403, "Seul un directeur peut valider ou rejeter un rapport.");
        }

        if (! $report->isSubmitted()) {
            abort(403, "Ce rapport doit d'abord être soumis par l'expert.");
        }
    }
}