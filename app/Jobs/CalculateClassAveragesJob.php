<?php

namespace App\Jobs;

use App\Models\Classe;
use App\Models\Term;
use App\Models\SchoolYear;
use App\Services\MarkCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateClassAveragesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    protected $classe;
    protected $term;
    protected $schoolYear;

    public function __construct(Classe $classe, Term $term, SchoolYear $schoolYear)
    {
        $this->classe = $classe;
        $this->term = $term;
        $this->schoolYear = $schoolYear;
    }

    public function handle(MarkCalculationService $calculationService)
    {
        Log::info('Début calcul asynchrone des moyennes', [
            'classe' => $this->classe->id,
            'term' => $this->term->id,
            'school_year' => $this->schoolYear->id
        ]);

        try {
            // Calculer les moyennes
            $calculationService->calculateAllSubjectAverages($this->classe, $this->term, $this->schoolYear);
            $calculationService->calculateGeneralAverages($this->classe, $this->term, $this->schoolYear);

            Log::info('Calcul asynchrone terminé avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur dans le job de calcul: ' . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        Log::error('Job de calcul échoué: ' . $exception->getMessage());
    }
}
