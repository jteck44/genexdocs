<?php

namespace App\Http\Controllers;

use App\Models\ReportCategory;

class ReportCategoryController extends Controller
{
    // Écran 1 du parcours normal (pas de l'import) : la liste des
    // catégories, cliquables, pour démarrer la rédaction d'un rapport.
    public function index()
    {
        // withCount('types') ajoute à chaque catégorie un attribut
        // "types_count" (nombre de types dedans), calculé directement
        // par la base de données — pratique pour l'affichage
        // ("Automobile — 3 types de rapport disponibles").
        $categories = ReportCategory::withCount('types')->orderBy('name')->get();

        return view('report_categories.index', compact('categories'));
    }
}
