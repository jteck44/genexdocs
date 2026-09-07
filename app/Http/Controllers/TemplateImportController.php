<?php

namespace App\Http\Controllers;

use App\Models\ReportCategory;
use App\Models\ReportType;
use App\Services\TemplateAnalyzerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TemplateImportController extends Controller
{
    public function __construct(private TemplateAnalyzerService $analyzer)
    {
    }

    private function authorizeDirector(): void
    {
        if (! Auth::user()->isDirector()) {
            abort(403, "Seul un directeur peut importer un nouveau template.");
        }
    }

    public function create()
    {
        $this->authorizeDirector();

        $categories = ReportCategory::with('types')->orderBy('name')->get();

        return view('templates.create', compact('categories'));
    }

    public function analyze(Request $request)
    {
        $this->authorizeDirector();

        $validated = $request->validate([
            'category_id' => 'nullable|exists:report_categories,id',
            'new_category_name' => 'nullable|string|max:255',
            'type_name' => 'required|string|max:255',
            'template_file' => 'required|file|mimes:docx|max:10240',
        ]);

        $tempPath = $request->file('template_file')->store('tmp_templates');

        $analysis = $this->analyzer->analyze($request->file('template_file'));

        $suggestedFields = [];
        foreach ($analysis['variables'] as $variable) {
            if ($variable === 'logo') {
                continue;
            }

            $suggestedFields[] = [
                'key' => $variable,
                'label' => ucfirst(str_replace('_', ' ', $variable)),
                'type' => $this->analyzer->guessFieldType($variable),
            ];
        }

        return view('templates.confirm', [
            'tempPath' => $tempPath,
            'categoryId' => $validated['category_id'] ?? null,
            'newCategoryName' => $validated['new_category_name'] ?? null,
            'typeName' => $validated['type_name'],
            'suggestedFields' => $suggestedFields,
            'hasLogo' => $analysis['has_logo'],
            'noVariablesFound' => empty($analysis['variables']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeDirector();

        $validated = $request->validate([
            'temp_path' => 'required|string',
            'category_id' => 'nullable|exists:report_categories,id',
            'new_category_name' => 'nullable|string|max:255',
            'type_name' => 'required|string|max:255',
            'field_keys' => 'required|array',
            'field_labels' => 'required|array',
            'field_types' => 'required|array',
        ]);

        if (! empty($validated['category_id'])) {
            $category = ReportCategory::findOrFail($validated['category_id']);
        } else {
            $name = $validated['new_category_name'];
            $category = ReportCategory::create([
                'name' => $name,
                'slug' => ReportCategory::makeUniqueSlug($name),
            ]);
        }

        $fields = array_map(
            fn ($key, $label, $type) => ['key' => $key, 'label' => $label, 'type' => $type],
            $validated['field_keys'],
            $validated['field_labels'],
            $validated['field_types']
        );

        // --- Ajout du tableau façon "mini-Excel", si le directeur en a défini un ---
        if ($request->boolean('has_table') && $request->filled('table_key')) {
            $columns = array_map(
                fn ($key, $label, $type) => ['key' => $key, 'label' => $label, 'type' => $type],
                $request->input('table_column_keys', []),
                $request->input('table_column_labels', []),
                $request->input('table_column_types', [])
            );

            $columns = array_values(array_filter($columns, fn ($c) => $c['key'] !== ''));

            $tableField = [
                'key' => $request->input('table_key'),
                'label' => $request->input('table_label') ?: $request->input('table_key'),
                'type' => 'table',
                'columns' => $columns,
                'show_total' => $request->boolean('show_total'),
            ];

            if ($request->filled('computed_target') && $request->filled('computed_factor_1') && $request->filled('computed_factor_2')) {
                $tableField['computed'] = [
                    'target' => $request->input('computed_target'),
                    'factors' => [$request->input('computed_factor_1'), $request->input('computed_factor_2')],
                ];
            }

            $fields[] = $tableField;
        }

        $slug = ReportType::makeUniqueSlug($validated['type_name']);

        $tempAbsolutePath = Storage::path($validated['temp_path']);
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempAbsolutePath,
            basename($tempAbsolutePath),
            null,
            null,
            true
        );

        $storeOutcome = DB::transaction(function () use ($category, $validated, $fields, $slug, $uploadedFile) {
            $reportType = ReportType::create([
                'report_category_id' => $category->id,
                'name' => $validated['type_name'],
                'slug' => $slug,
                'template_path' => '',
                'fields' => $fields,
                'has_logo_placeholder' => false,
                'imported_by' => Auth::id(),
            ]);

            $storeResult = $this->analyzer->storeTemplate($uploadedFile, $slug);

            $reportType->update([
                'template_path' => $storeResult['path'],
                'has_logo_placeholder' => $storeResult['logo_status'] !== 'no_header_available',
            ]);

            return [
                'reportType' => $reportType,
                'logoStatus' => $storeResult['logo_status'],
            ];
        });

        Storage::delete($validated['temp_path']);

        $reportType = $storeOutcome['reportType'];

        $statusMessage = match ($storeOutcome['logoStatus']) {
            'present' => "Le type de rapport « {$reportType->name} » a bien été importé (logo déjà présent dans le modèle).",
            'added' => "Le type de rapport « {$reportType->name} » a bien été importé (repère logo ajouté automatiquement).",
            'no_header_available' => "Le type de rapport « {$reportType->name} » a été importé, mais ce document Word n'a "
                ."aucune zone d'en-tête existante : ajoutez-en une manuellement dans Word (Insertion > En-tête) "
                ."avec le repère \${logo} dedans, puis réimportez ce template.",
        };

        return redirect()
            ->route('report-types.index', $category->slug)
            ->with('status', $statusMessage);
    }

    /**
     * Suppression d'un template : masquage (soft delete) s'il est lié à
     * des rapports existants, suppression réelle sinon.
     */
    public function destroy(ReportType $reportType)
    {
        $this->authorizeDirector();

        $reportsCount = $reportType->reports()->count();

        if ($reportsCount > 0) {
            $reportType->delete();

            return back()->with('status',
                "« {$reportType->name} » a été retiré de la liste. Les {$reportsCount} rapport(s) "
                ."déjà créés avec ce modèle continuent de fonctionner normalement."
            );
        }

        Storage::delete($reportType->template_path);
        $reportType->forceDelete();

        return back()->with('status', "« {$reportType->name} » a été définitivement supprimé.");
    }

    /**
     * Utilisée par la page pour préparer le bon message d'avertissement
     * AVANT la première des deux fenêtres de confirmation.
     */
    public function checkDeletable(ReportType $reportType)
    {
        $this->authorizeDirector();

        return response()->json([
            'reports_count' => $reportType->reports()->count(),
        ]);
    }

    /**
     * Liste des templates masqués (la "corbeille").
     */
    public function archived()
    {
        $this->authorizeDirector();

        $archivedTypes = ReportType::onlyTrashed()
            ->with('category')
            ->withCount('reports')
            ->orderBy('name')
            ->get();

        return view('templates.archived', compact('archivedTypes'));
    }

    /**
     * Fait réapparaître un template masqué.
     */
    public function restore(int $id)
    {
        $this->authorizeDirector();

        $reportType = ReportType::onlyTrashed()->findOrFail($id);

        $reportType->restore();

        return redirect()
            ->route('report-types.index', $reportType->category->slug)
            ->with('status', "« {$reportType->name} » a été restauré et réapparaît dans la liste.");
    }
}