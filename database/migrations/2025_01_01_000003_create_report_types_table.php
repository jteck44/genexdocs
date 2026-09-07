<?php
// ============================================================================
// MIGRATION : table des TYPES de rapport (ex: "Rapport après travaux",
// "Note préliminaire"...). Chaque type appartient à UNE catégorie, et
// porte lui-même son template Word + la liste de ses champs variables.
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_types', function (Blueprint $table) {
            $table->id();

            // foreignId() = crée une colonne "report_category_id" (nombre entier)
            // ->constrained() = dit à MySQL "cette colonne doit correspondre à un
            //   id qui existe vraiment dans la table report_categories" — la base
            //   de données elle-même refusera d'enregistrer un type rattaché à
            //   une catégorie inexistante, c'est une sécurité automatique.
            // ->cascadeOnDelete() = si on supprime une catégorie, tous ses types
            //   sont supprimés avec elle automatiquement (évite les orphelins).
            $table->foreignId('report_category_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('name');          // ex: "Rapport après travaux"
            $table->string('slug')->unique();

            // Chemin du fichier .docx modèle, stocké sur le disque du serveur
            // (pas dans le navigateur) — ex: "report_templates/rapport-apres-travaux.docx"
            $table->string('template_path');

            // json() = une colonne qui stocke une LISTE structurée d'informations,
            // pas juste un texte simple. On y mettra la liste des champs variables
            // du template, par exemple :
            // [{"key":"client_nom","label":"Nom du client","type":"text"}, ...]
            // C'est ce qui permet au formulaire de s'adapter automatiquement
            // à chaque type de rapport, sans coder un formulaire par type.
            $table->json('fields');

            // Le template contient-il bien un repère ${logo} exploitable ?
            // (toujours "true" avec notre système, puisqu'on l'ajoute automatiquement
            // à l'import si absent — voir TemplateAnalyzerService) mais on garde
            // cette information en base pour l'affichage et la traçabilité.
            $table->boolean('has_logo_placeholder')->default(false);

            // Qui a importé ce template, et quand (traçabilité)
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_types');
    }
};
