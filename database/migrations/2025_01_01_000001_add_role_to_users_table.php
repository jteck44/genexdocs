<?php
// ============================================================================
// MIGRATION : ajoute le champ "role" à la table des utilisateurs déjà créée
// par Laravel Breeze (users). Une migration = un "plan" de modification
// de la base de données, versionné, que l'on peut exécuter ou annuler.
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * up() = ce qui se passe quand on APPLIQUE la migration
     * (commande : php artisan migrate)
     */
    public function up(): void
    {
        // On modifie une table déjà existante avec Schema::table
        // (par opposition à Schema::create, utilisé pour une nouvelle table)
        Schema::table('users', function (Blueprint $table) {
            // enum() = une colonne qui n'accepte QUE les valeurs listées.
            // Impossible d'y stocker autre chose que 'expert' ou 'director'.
            // ->default('expert') = valeur automatique si on ne précise rien
            // ->after('email') = purement esthétique, place juste la colonne
            //                     juste après "email" quand on regarde la table
            $table->enum('role', ['expert', 'director'])
                  ->default('expert')
                  ->after('email');
        });
    }

    /**
     * down() = ce qui se passe si on ANNULE la migration
     * (commande : php artisan migrate:rollback)
     * Toujours prévoir l'annulation, c'est une bonne pratique.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
