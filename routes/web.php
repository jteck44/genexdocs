<?php

use App\Http\Controllers\MandantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportTypeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateImportController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('accueil');
});
Route::get('/test', function () {
    return 'je commence le projet avec ce test';
});

Route::prefix('genexdocs')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::middleware('auth')->group(function () {
    // --- Profil (Breeze) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Catégories / Types / Rapports ---
    Route::get('/categories', [ReportCategoryController::class, 'index'])->name('report-categories.index');
    Route::get('/categories/{categorySlug}/types', [ReportTypeController::class, 'index'])->name('report-types.index');

    Route::get('/rapports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/types/{typeSlug}/nouveau-rapport', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/types/{typeSlug}/nouveau-rapport', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/rapports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::put('/rapports/{report}', [ReportController::class, 'update'])->name('reports.update');
    Route::delete('/rapports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
    Route::delete('/rapports/{report}/direction', [ReportController::class, 'destroyAsDirector'])->name('reports.director-destroy');   

    Route::get('/rapports/{report}/export-brouillon', [ReportController::class, 'exportDraft'])->name('reports.export-draft');
    Route::post('/rapports/{report}/soumettre', [ReportController::class, 'submit'])->name('reports.submit');
    Route::post('/rapports/{report}/rejeter', [ReportController::class, 'reject'])->name('reports.reject');
    Route::post('/rapports/{report}/valider', [ReportController::class, 'validateReport'])->name('reports.validate');
    Route::get('/rapports/{report}/telecharger-final', [ReportController::class, 'downloadValidated'])->name('reports.download-validated');

    // --- Import et gestion des templates (directeur) ---
    Route::get('/templates/importer', [TemplateImportController::class, 'create'])->name('templates.create');
    Route::post('/templates/importer/analyser', [TemplateImportController::class, 'analyze'])->name('templates.analyze');
    Route::post('/templates/importer/enregistrer', [TemplateImportController::class, 'store'])->name('templates.store');
    Route::delete('/templates/{reportType}', [TemplateImportController::class, 'destroy'])->name('templates.destroy');
    Route::get('/templates/{reportType}/verifiable', [TemplateImportController::class, 'checkDeletable'])->name('templates.check-deletable');
    Route::get('/templates/archives', [TemplateImportController::class, 'archived'])->name('templates.archived');
    Route::post('/templates/{id}/restaurer', [TemplateImportController::class, 'restore'])->name('templates.restore');

    // --- Mandants ---
    Route::get('/mandants', [MandantController::class, 'index'])->name('mandants.index');
    Route::get('/mandants/{mandant}', [MandantController::class, 'show'])->name('mandants.show');
    Route::post('/mandants', [MandantController::class, 'store'])->name('mandants.store');
    Route::delete('/mandants/{mandant}', [MandantController::class, 'destroy'])->name('mandants.destroy');

    // --- Équipe et paramètres de direction ---
    Route::get('/equipe', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/equipe', [UserManagementController::class, 'store'])->name('users.store');
    Route::post('/equipe/{user}/reinitialiser-mot-de-passe', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/equipe/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    Route::get('/parametres', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/parametres/adjoint', [SettingsController::class, 'setAdjoint'])->name('settings.set-adjoint');
    Route::delete('/parametres/adjoint', [SettingsController::class, 'removeAdjoint'])->name('settings.remove-adjoint');
    });
});

require __DIR__.'/auth.php';