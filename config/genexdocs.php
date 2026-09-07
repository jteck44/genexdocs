<?php

// ============================================================================
// CONFIGURATION : où se trouve le logo Genex sur le disque du serveur.
// Un fichier de config Laravel (dans config/) est simplement un fichier
// qui renvoie un tableau PHP — on y accède ensuite avec config('genexdocs.logo_path').
// ============================================================================

return [
    // env('GENEXDOCS_LOGO_PATH', ...) : Laravel regarde d'abord si une
    // variable GENEXDOCS_LOGO_PATH existe dans le fichier .env ; sinon,
    // il utilise la valeur par défaut donnée en second argument.
    //
    // ACTION POUR VOUS : déposez votre fichier logo.png dans
    // storage/app/genex/logo.png (créez les dossiers s'ils n'existent pas),
    // OU ajoutez cette ligne dans votre fichier .env avec votre propre chemin :
    // GENEXDOCS_LOGO_PATH="C:\chemin\vers\votre\logo.png"
    'logo_path' => env('GENEXDOCS_LOGO_PATH', storage_path('app/genex/logo.png')),
];
