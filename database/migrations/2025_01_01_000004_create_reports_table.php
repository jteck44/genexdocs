<?php
// ============================================================================
// MIGRATION : table des RAPPORTS eux-mêmes — chaque ligne = un dossier
// en cours de rédaction ou terminé, rattaché à un type et à un expert.
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('report_type_id')->constrained()->cascadeOnDelete();

            // L'expert qui a créé/rédigé le rapport
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();

            // Le directeur qui a validé ou rejeté (vide tant que personne n'a statué)
            $table->foreignId('director_id')->nullable()->constrained('users')->nullOnDelete();

            // Un petit titre lisible pour retrouver le dossier dans une liste
            // (ex: "Sinistre SCDP NSAM - Canon à mousse")
            $table->string('title');

            // Toutes les valeurs saisies par l'expert pour les champs variables
            // du type de rapport, stockées ensemble sous forme structurée :
            // {"client_nom": "SCDP NSAM", "date_sinistre": "2025-09-08", ...}
            $table->json('data');

            // enum() = le statut ne peut être QUE l'une de ces 4 valeurs.
            // C'est exactement le cycle de vie qu'on avait modélisé :
            // draft -> submitted -> validated (ou rejected -> retour à draft)
            $table->enum('status', ['draft', 'submitted', 'validated', 'rejected'])
                  ->default('draft');

            // Rempli uniquement quand le directeur rejette, pour expliquer pourquoi
            $table->text('rejection_reason')->nullable();

            // Chemin du dernier export "brouillon" généré par l'expert (sans logo)
            $table->string('draft_export_path')->nullable();

            // Chemin du document final généré par le directeur (avec logo, officiel)
            $table->string('validated_path')->nullable();

            // Dates clés du cycle de vie, utiles pour un futur tableau de bord
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
