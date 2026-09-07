<?php

namespace Database\Seeders;

use App\Models\ReportCategory;
use Illuminate\Database\Seeder;

// ============================================================================
// SEEDER : un "seeder" (de l'anglais "semer") sert à remplir la base de
// données avec des données de départ, une fois, via une commande —
// pratique pour ne pas avoir à tout recréer à la main à chaque fois qu'on
// réinstalle le projet (par exemple sur une autre machine).
// ============================================================================
class ReportCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Automobile', 'Risque divers', 'Infrastructures'];

        foreach ($categories as $name) {
            // firstOrCreate() : cherche une ligne avec ce nom ; si elle
            // n'existe pas, la crée. Permet de relancer le seeder plusieurs
            // fois sans jamais créer de doublons.
            ReportCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => ReportCategory::makeUniqueSlug($name)]
            );
        }
    }
}
