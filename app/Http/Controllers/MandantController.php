<?php

namespace App\Http\Controllers;

use App\Models\Mandant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MandantController extends Controller
{
    /**
    * Liste des mandants, avec recherche par nom.
     */
    public function index(Request $request)
    {
        // $request->query('q') : récupère ce qui a été tapé dans la barre
        // de recherche (paramètre "q" dans l'URL, ex: /mandants?q=nsam).
        $search = $request->query('q');

        $mandants = Mandant::withCount('reports')
            // when() : n'applique la condition suivante QUE si $search
            // n'est pas vide — évite un "if" séparé autour de la requête.
            ->when($search, function ($query, $search) {
                // "like" avec % de chaque côté = "contient ce texte",
                // pas seulement "commence par" ou "est exactement".
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        return view('mandants.index', compact('mandants', 'search'));
    }

    /**
    * Fiche d'un mandant : ses informations + tous ses rapports.
     */
    public function show(Mandant $mandant)
    {
        $reports = $mandant->reports()
            ->with('type.category')
            ->when(request('category'), function ($query, $categorySlug) {
                $query->whereHas('type.category', fn ($q) => $q->where('slug', $categorySlug));
            })
            ->latest()
            ->get();

        return view('mandants.show', compact('mandant', 'reports'));
    }

    /**
    * Création rapide (utilisée aussi bien depuis la liste des mandants
     * que depuis le formulaire de rédaction d'un rapport).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $mandant = Mandant::create($validated);

        return redirect()->route('mandants.show', $mandant->id)
            ->with('status', "Mandant « {$mandant->name} » créé.");
    }

    public function destroy(Mandant $mandant)
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->isChiefDirector(), 403, 'Seul le directeur en titre peut supprimer un mandant.');

        $name = $mandant->name;
        $mandant->delete();

        return redirect()->route('mandants.index')
            ->with('status', "Mandant « {$name} » supprimé.");
    }
}