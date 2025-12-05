<?php
// app/Console/Commands/CalculateAverages.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Classe;
use App\Models\Term;
use App\Models\SchoolYear;
use App\Services\MarkCalculationService;

class CalculateAverages extends Command
{
    protected $signature = 'averages:calculate {class?} {--term=} {--year=}';
    protected $description = 'Forcer le calcul des moyennes';

    protected $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        parent::__construct();
        $this->calculationService = $calculationService;
    }

    public function handle()
    {
        $classId = $this->argument('class');
        $termId = $this->option('term');
        $yearId = $this->option('year');

        // Résoudre les paramètres
        $class = $classId ? Classe::find($classId) : Classe::first();
        $term = $termId ? Term::find($termId) : Term::current();
        $schoolYear = $yearId ? SchoolYear::find($yearId) : SchoolYear::current();

        if (!$class) {
            $this->error('Aucune classe trouvée');
            return 1;
        }

        $this->info("Calcul des moyennes pour la classe: {$class->name}");
        $this->info("Trimestre: {$term->name}");
        $this->info("Année scolaire: {$schoolYear->year}");

        // Calculer les moyennes
        $this->info("Calcul des moyennes par matière...");
        $result1 = $this->calculationService->calculateAllSubjectAverages($class, $term, $schoolYear);
        
        $this->info("Calcul des moyennes générales...");
        $result2 = $this->calculationService->calculateGeneralAverages($class, $term, $schoolYear);

        if ($result1 && $result2) {
            $this->info("✓ Calcul terminé avec succès!");
            
            // Afficher les statistiques
            $stats = $this->calculationService->calculateClassStatistics($class->id, $term->id, $schoolYear->id);
            $this->table(
                ['Statistique', 'Valeur'],
                [
                    ['Moyenne classe', $stats['class_average']],
                    ['Min', $stats['min_average']],
                    ['Max', $stats['max_average']],
                    ['Taux réussite', $stats['success_rate'] . '%'],
                    ['Effectif', $stats['total_students']],
                ]
            );
        } else {
            $this->error("✗ Erreur lors du calcul");
        }

        return 0;
    }
}