<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Commande personnalisée pour le calcul des moyennes
Artisan::command('school:calculate-averages {term?} {school_year?}', function ($term = null, $schoolYear = null) {
    $this->info('Début du calcul des moyennes...');

    // Logique de calcul des moyennes
    $calculationService = app(\App\Services\MarkCalculationService::class);

    if (!$term) {
        $term = \App\Models\Term::current();
    } else {
        $term = \App\Models\Term::find($term);
    }

    if (!$schoolYear) {
        $schoolYear = \App\Models\SchoolYear::current();
    } else {
        $schoolYear = \App\Models\SchoolYear::find($schoolYear);
    }

    if ($term && $schoolYear) {
        $calculationService->recalculateAllAverages($term, $schoolYear);
        $this->info("Moyennes calculées pour le trimestre {$term->name} et l'année {$schoolYear->year}");
    } else {
        $this->error('Term ou SchoolYear non trouvé');
    }
})->purpose('Recalculer toutes les moyennes');

// Commande pour générer des données de test
Artisan::command('school:generate-test-data', function () {
    $this->info('Génération des données de test...');

    // Exécuter les seeders
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);

    $this->info('Données de test générées avec succès');
})->purpose('Générer des données de test pour l\'application');

// Commande pour nettoyer les anciens audits
Artisan::command('school:clean-audits {--days=30}', function () {
    $days = $this->option('days');
    $date = now()->subDays($days);

    $deleted = \OwenIt\Auditing\Models\Audit::where('created_at', '<', $date)->delete();

    $this->info("{$deleted} enregistrements d'audit supprimés (plus anciens que {$days} jours)");
})->purpose('Nettoyer les anciens enregistrements d\'audit');
