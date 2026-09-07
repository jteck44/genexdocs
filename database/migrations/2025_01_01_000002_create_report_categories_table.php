<?php
// ============================================================================
// MIGRATION : table des CATÉGORIES de rapport
// Exemple de lignes qu'elle contiendra : "Automobile", "Risque divers",
// "Infrastructures" — le premier niveau de choix pour l'utilisateur.
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::create = crée une TOUTE NOUVELLE table (n'existe pas encore)
        Schema::create('report_categories', function (Blueprint $table) {
            // id() = crée automatiquement une colonne "id", nombre entier,
            // qui s'incrémente tout seul (1, 2, 3...) et sert de clé primaire
            // (l'identifiant unique de chaque ligne).
            $table->id();

            // string() = texte court (limité à 255 caractères par défaut)
            $table->string('name');        // ex: "Automobile"

            // slug = une version "propre" du nom, sans espaces ni accents,
            // utilisée dans les URLs (ex: /categories/automobile).
            // ->unique() = impossible d'avoir deux fois le même slug
            $table->string('slug')->unique();

            // timestamps() = ajoute automatiquement deux colonnes :
            // created_at et updated_at, remplies toutes seules par Laravel
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // dropIfExists = supprime la table si elle existe (annulation propre)
        Schema::dropIfExists('report_categories');
    }
};
