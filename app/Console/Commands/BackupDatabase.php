<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

// ============================================================================
// COMMANDE ARTISAN PERSONNALISÉE : php artisan genexdocs:backup
// On peut créer nos propres commandes, exactement comme celles fournies
// par Laravel (migrate, serve...) — utile pour des tâches répétitives.
// ============================================================================
class BackupDatabase extends Command
{
    // $signature = comment on l'appelle dans le terminal
    protected $signature = 'genexdocs:backup';

    // $description = ce qui s'affiche dans "php artisan list"
    protected $description = 'Sauvegarde la base de données SQLite avec un horodatage';

    public function handle(): void
    {
        $source = database_path('database.sqlite');
        $backupDir = storage_path('app/backups');

        if (! File::exists($source)) {
            $this->error("Base de données introuvable à : {$source}");

            return;
        }

        File::ensureDirectoryExists($backupDir);

        $destination = $backupDir.'/database-'.now()->format('Y-m-d_His').'.sqlite';
        File::copy($source, $destination);

        $this->info("Sauvegarde créée : {$destination}");

        // On garde seulement les 10 dernières sauvegardes, pour ne pas
        // accumuler indéfiniment des fichiers sur le disque.
        $backups = collect(File::files($backupDir))
            ->sortByDesc(fn ($file) => $file->getMTime());

        $backups->skip(10)->each(fn ($file) => File::delete($file->getPathname()));
    }
}