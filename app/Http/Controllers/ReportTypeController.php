<?php

namespace App\Http\Controllers;

use App\Models\ReportCategory;

class ReportTypeController extends Controller
{
    // Le paramètre $categorySlug vient directement de l'URL, grâce à la
    // route qu'on définira comme : /categories/{categorySlug}/types
    public function index(string $categorySlug)
    {
        // firstOrFail() : va chercher la catégorie correspondant à ce slug,
        // et arrête tout avec une page "404 introuvable" si elle n'existe pas
        // — évite d'avoir à vérifier nous-mêmes "si null, alors erreur".
        $category = ReportCategory::where('slug', $categorySlug)->firstOrFail();

        $types = $category->types()->orderBy('name')->get();

        return view('report_types.index', compact('category', 'types'));
    }
}
