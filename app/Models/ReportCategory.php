<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

// ============================================================================
// MODELE : ReportCategory
// Un "modèle" Eloquent représente UNE ligne de table, mais donne accès
// à TOUTES les lignes et à leurs relations avec d'autres tables.
// Rappel : Laravel devine tout seul que ce modèle correspond à la table
// "report_categories" (pluriel du nom de la classe, en minuscules).
// ============================================================================
class ReportCategory extends Model
{
    use HasFactory;

    // $fillable = liste blanche des colonnes qu'on autorise à remplir
    // via un formulaire (ReportCategory::create($request->all())).
    // Sécurité : sans ça, un utilisateur malveillant pourrait injecter
    // des champs qu'on n'a pas prévus dans le formulaire.
    protected $fillable = ['name', 'slug'];

    /**
     * Relation : une catégorie POSSÈDE PLUSIEURS types de rapport.
     * hasMany() dit à Laravel : "va chercher dans la table report_types
     * toutes les lignes dont report_category_id correspond à mon id".
     * Grâce à ça, on pourra écrire $categorie->types pour lister
     * tous les types d'une catégorie, sans écrire de requête SQL.
     */
    public function types(): HasMany
    {
        return $this->hasMany(ReportType::class);
    }

    /**
     * Petite méthode utilitaire : génère automatiquement un slug unique
     * à partir du nom (ex: "Risque Divers" -> "risque-divers").
     * static:: veut dire "utilise la classe actuelle" (ici ReportCategory).
     */
    public static function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name); // Str::slug() fait la conversion texte -> slug
        $slug = $base;
        $i = 1;

        // Tant qu'un slug identique existe déjà en base, on ajoute -2, -3...
        // (gère le cas "deux catégories au nom très proche" qu'on avait anticipé)
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
