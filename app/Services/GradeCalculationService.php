<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\Term;
use App\Models\SchoolYear;
use App\Models\Evaluation;
use App\Models\Mark;
use App\Models\Average;
use App\Models\GeneralAverage;
use App\Models\ExamType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MarkCalculationService
{
    const CACHE_TTL = 3600; // 1 heure

    /**
     * Calculer la moyenne d'un élève pour une matière et un trimestre
     */

     public function calculateSubjectAverage(Student $student, Subject $subject, Term $term, SchoolYear $schoolYear)
{
    try {
        Log::info("Calcul moyenne matière", [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'term_id' => $term->id,
            'school_year_id' => $schoolYear->id,
            'class_id' => $student->class_id
        ]);

        // Vérifier si l'étudiant a une classe
        if (!$student->class_id) {
            Log::warning("Étudiant sans classe: {$student->id}");
            return null;
        }

        // Récupérer les évaluations pour cette matière
        $evaluations = Evaluation::where('class_id', $student->class_id)
            ->where('subject_id', $subject->id)
            ->where('term_id', $term->id)
            ->where('school_year_id', $schoolYear->id)
            ->with('examType')
            ->get();

        Log::info("Évaluations trouvées: " . $evaluations->count());

        if ($evaluations->isEmpty()) {
            Log::warning("Aucune évaluation pour la matière {$subject->name}");
            return null;
        }

        $totalWeightedMarks = 0;
        $totalWeight = 0;
        $hasMarks = false;

        foreach ($evaluations as $evaluation) {
            $mark = Mark::where('student_id', $student->id)
                ->where('evaluation_id', $evaluation->id)
                ->first();

            if ($mark && !$mark->is_absent && $mark->marks !== null) {
                $weight = $evaluation->examType->weight ?? 1;
                $totalWeightedMarks += $mark->marks * $weight;
                $totalWeight += $weight;
                $hasMarks = true;
                Log::debug("Note trouvée: {$mark->marks} (poids: {$weight})");
            } else {
                Log::debug("Note absente ou nulle pour évaluation {$evaluation->id}");
            }
        }

        if (!$hasMarks) {
            Log::warning("Aucune note valide pour l'étudiant {$student->id}");
            return null;
        }

        if ($totalWeight == 0) {
            Log::warning("Poids total nul pour l'étudiant {$student->id}");
            return null;
        }

        $average = round($totalWeightedMarks / $totalWeight, 2);
        Log::info("Moyenne calculée: {$average} pour {$subject->name}");

        return $average;
    } catch (\Exception $e) {
        Log::error("Erreur calcul moyenne matière: " . $e->getMessage());
        Log::error("Stack trace: " . $e->getTraceAsString());
        return null;
    }
}

// Ajouter une méthode pour vérifier les données avant calcul
private function validateCalculationParameters($class, $term, $schoolYear)
{
    $validator = Validator::make([
        'class_id' => $class->id,
        'term_id' => $term->id,
        'school_year_id' => $schoolYear->id
    ], [
        'class_id' => 'required|exists:classes,id',
        'term_id' => 'required|exists:terms,id',
        'school_year_id' => 'required|exists:school_years,id'
    ]);

    if ($validator->fails()) {
        throw new ValidationException($validator);
    }
}

    // public function calculateSubjectAverage(Student $student, Subject $subject, Term $term, SchoolYear $schoolYear)
    // {
    //     $cacheKey = "subject_avg_{$student->id}_{$subject->id}_{$term->id}_{$schoolYear->id}";

    //     return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($student, $subject, $term, $schoolYear) {
    //         $evaluations = Evaluation::where('class_id', $student->class_id)
    //             ->where('subject_id', $subject->id)
    //             ->where('term_id', $term->id)
    //             ->where('school_year_id', $schoolYear->id)
    //             ->with('examType')
    //             ->get();

    //         if ($evaluations->isEmpty()) {
    //             return null;
    //         }

    //         $totalWeightedMarks = 0;
    //         $totalWeight = 0;

    //         foreach ($evaluations as $evaluation) {
    //             $mark = Mark::where('student_id', $student->id)
    //                 ->where('evaluation_id', $evaluation->id)
    //                 ->first();

    //             if ($mark && !$mark->is_absent) {
    //                 $weight = $evaluation->examType->weight ?? 1;
    //                 $totalWeightedMarks += $mark->marks * $weight;
    //                 $totalWeight += $weight;
    //             }
    //         }

    //         if ($totalWeight == 0) {
    //             return null;
    //         }

    //         return round($totalWeightedMarks / $totalWeight, 2);
    //     });
    // }

    /**
     * Calculer toutes les moyennes par matière pour une classe
     */

    public function calculateAllSubjectAverages(Classe $classe, Term $term, SchoolYear $schoolYear)
{
    DB::beginTransaction();

    try {
        Log::info("=== DÉBUT CALCUL MOYENNES MATIÈRES ===", [
            'classe' => $classe->id,
            'classe_nom' => $classe->name,
            'term' => $term->id,
            'term_nom' => $term->name,
            'school_year' => $schoolYear->id,
            'school_year_nom' => $schoolYear->year
        ]);

        // Vérifier que la classe a des étudiants
        $students = $classe->students()
            ->whereNotNull('class_id')
            ->where('is_active', true)
            ->get();

        Log::info("Nombre d'étudiants dans la classe: " . $students->count());

        if ($students->isEmpty()) {
            Log::warning("Aucun étudiant dans la classe {$classe->id}");
            DB::rollBack();
            return false;
        }

        // Récupérer toutes les matières actives
        $subjects = Subject::where('is_active', true)->get();
        Log::info("Nombre de matières actives: " . $subjects->count());

        if ($subjects->isEmpty()) {
            Log::warning("Aucune matière active");
            DB::rollBack();
            return false;
        }

        $processed = 0;
        $failed = 0;

        foreach ($students as $student) {
            Log::info("Traitement étudiant: {$student->full_name} (ID: {$student->id})");

            foreach ($subjects as $subject) {
                try {
                    $average = $this->calculateSubjectAverage($student, $subject, $term, $schoolYear);

                    if ($average !== null) {
                        Average::updateOrCreate(
                            [
                                'student_id' => $student->id,
                                'subject_id' => $subject->id,
                                'class_id' => $classe->id,
                                'term_id' => $term->id,
                                'school_year_id' => $schoolYear->id,
                            ],
                            [
                                'average' => $average,
                                'appreciation' => $this->getAppreciation($average),
                            ]
                        );
                        $processed++;
                        Log::debug("Moyenne enregistrée: {$average} pour {$subject->name}");
                    } else {
                        Log::debug("Moyenne nulle pour {$subject->name}, étudiant {$student->id}");
                        $failed++;
                    }
                } catch (\Exception $e) {
                    Log::error("Erreur calcul moyenne étudiant {$student->id}, matière {$subject->id}: " . $e->getMessage());
                    $failed++;
                    continue;
                }
            }
        }

        DB::commit();

        Log::info("=== FIN CALCUL MOYENNES MATIÈRES ===", [
            'moyennes_calculées' => $processed,
            'calculs_échoués' => $failed,
            'étudiants' => $students->count(),
            'matières' => $subjects->count()
        ]);

        // Vérifier que des moyennes ont été calculées
        if ($processed == 0) {
            Log::error("Aucune moyenne calculée !");
            return false;
        }

        return true;

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("=== ERREUR CRITIQUE CALCUL MOYENNES ===");
        Log::error("Message: " . $e->getMessage());
        Log::error("Fichier: " . $e->getFile());
        Log::error("Ligne: " . $e->getLine());
        Log::error("Stack trace: " . $e->getTraceAsString());

        return false;
    }
}

    // public function calculateAllSubjectAverages(Classe $class, Term $term, SchoolYear $schoolYear)
    // {
    //     $students = $class->students()->get();
    //     $subjects = Subject::active()->get();

    //     DB::transaction(function () use ($students, $subjects, $class, $term, $schoolYear) {
    //         foreach ($students as $student) {
    //             foreach ($subjects as $subject) {
    //                 $average = $this->calculateSubjectAverage($student, $subject, $term, $schoolYear);

    //                 if ($average !== null) {
    //                     Average::updateOrCreate(
    //                         [
    //                             'student_id' => $student->id,
    //                             'subject_id' => $subject->id,
    //                             'class_id' => $class->id,
    //                             'term_id' => $term->id,
    //                             'school_year_id' => $schoolYear->id,
    //                         ],
    //                         [
    //                             'average' => $average,
    //                             'appreciation' => $this->getAppreciation($average),
    //                         ]
    //                     );
    //                 }
    //             }
    //         }
    //     });

    //     // Invalider le cache
    //     $this->invalidateClassCache($class->id, $term->id, $schoolYear->id);

    //     return true;
    // }

    /**
     * Calculer les moyennes générales pour une classe
     */

     public function calculateGeneralAverages(Classe $classe, Term $term, SchoolYear $schoolYear)
    {
        try {
            Log::info("Début calcul moyennes générales", [
                'classe' => $classe->id,
                'term' => $term->id,
                'school_year' => $schoolYear->id
            ]);

            $students = $classe->students()->with('user')->get();

            if ($students->isEmpty()) {
                Log::warning("Aucun étudiant pour le calcul des moyennes générales");
                return false;
            }

            DB::beginTransaction();

            $allAverages = [];

            foreach ($students as $student) {
                // Récupérer toutes les moyennes de l'étudiant
                $averages = Average::where('student_id', $student->id)
                    ->where('class_id', $classe->id)
                    ->where('term_id', $term->id)
                    ->where('school_year_id', $schoolYear->id)
                    ->with('subject')
                    ->get();

                $totalWeightedAverage = 0;
                $totalCoefficients = 0;

                foreach ($averages as $average) {
                    if ($average->average !== null && $average->subject) {
                        $coef = $average->subject->coefficient ?? 1;
                        $totalWeightedAverage += $average->average * $coef;
                        $totalCoefficients += $coef;
                    }
                }

                if ($totalCoefficients > 0) {
                    $generalAverage = round($totalWeightedAverage / $totalCoefficients, 2);
                    $allAverages[$student->id] = $generalAverage;
                } else {
                    $allAverages[$student->id] = 0;
                }
            }

            // Trier par moyenne décroissante
            arsort($allAverages);
            $rank = 1;

            foreach ($allAverages as $studentId => $average) {
                GeneralAverage::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'classe_id' => $classe->id,
                        'term_id' => $term->id,
                        'school_year_id' => $schoolYear->id,
                    ],
                    [
                        'average' => $average,
                        'rank' => $rank,
                        'appreciation' => $this->getAppreciation($average),
                        'total_students' => count($allAverages),
                    ]
                );
                $rank++;
            }

            DB::commit();

            Log::info("Calcul moyennes générales terminé", [
                'étudiants_traités' => count($allAverages)
            ]);

            $this->clearClassCache($classe->id, $term->id, $schoolYear->id);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur calcul moyennes générales: " . $e->getMessage());
            return false;
        }
    }

    // public function calculateGeneralAverages(Classe $class, Term $term, SchoolYear $schoolYear)
    // {
    //     $students = $class->students()->get();

    //     DB::transaction(function () use ($students, $class, $term, $schoolYear) {
    //         $allAverages = [];

    //         foreach ($students as $student) {
    //             $averages = Average::where('student_id', $student->id)
    //                 ->where('class_id', $class->id)
    //                 ->where('term_id', $term->id)
    //                 ->where('school_year_id', $schoolYear->id)
    //                 ->with('subject')
    //                 ->get();

    //             $totalWeightedAverage = 0;
    //             $totalCoefficients = 0;

    //             foreach ($averages as $average) {
    //                 if ($average->average !== null && $average->subject) {
    //                     $coef = $average->subject->coefficient ?? 1;
    //                     $totalWeightedAverage += $average->average * $coef;
    //                     $totalCoefficients += $coef;
    //                 }
    //             }
    //             // foreach ($averages as $average) {
    //             //     if ($average->average !== null) {
    //             //         $totalWeightedAverage += $average->average * $average->subject->coefficient;
    //             //         $totalCoefficients += $average->subject->coefficient;
    //             //     }
    //             // }

    //             if ($totalCoefficients > 0) {
    //                 $generalAverage = round($totalWeightedAverage / $totalCoefficients, 2);
    //                 $allAverages[$student->id] = $generalAverage;
    //             }
    //         }

    //         // Classer les élèves
    //         arsort($allAverages);
    //         $rank = 1;

    //         foreach ($allAverages as $studentId => $average) {
    //             GeneralAverage::updateOrCreate(
    //                 [
    //                     'student_id' => $studentId,
    //                     'class_id' => $class->id,
    //                     'term_id' => $term->id,
    //                     'school_year_id' => $schoolYear->id,
    //                 ],
    //                 [
    //                     'average' => $average,
    //                     'rank' => $rank,
    //                     'appreciation' => $this->getAppreciation($average),
    //                     'total_students' => count($allAverages),
    //                 ]
    //             );
    //             $rank++;
    //         }
    //     });

    //     $this->invalidateClassCache($class->id, $term->id, $schoolYear->id);
    //     return true;
    // }

    /**
     * Obtenir les résultats d'un étudiant
     */
    public function calculateStudentResults($studentId, $schoolYearId, $termId)
{
    Log::info("=== DÉBUT CALCUL RÉSULTATS ÉTUDIANT ===", [
        'student_id' => $studentId,
        'school_year_id' => $schoolYearId,
        'term_id' => $termId
    ]);

    try {
        $student = Student::with(['classe'])->find($studentId);

        if (!$student) {
            Log::error("Étudiant {$studentId} non trouvé");
            return $this->getEmptyResults();
        }

        if (!$student->class_id) {
            Log::warning("Étudiant {$studentId} n'a pas de classe assignée");
            return $this->getEmptyResults();
        }

        $term = Term::find($termId);
        $schoolYear = SchoolYear::find($schoolYearId);

        if (!$term || !$schoolYear) {
            Log::error("Term ou SchoolYear non trouvés");
            return $this->getEmptyResults();
        }

        // Vérifier si des moyennes existent déjà
        $averages = Average::where('student_id', $studentId)
            ->where('school_year_id', $schoolYearId)
            ->where('term_id', $termId)
            ->where('class_id', $student->class_id)
            ->with('subject')
            ->get();

        Log::info("Moyennes existantes: " . $averages->count());

        // Si pas de moyennes, tenter de les calculer
        if ($averages->isEmpty()) {
            Log::info("Aucune moyenne trouvée, tentative de calcul...");

            $classe = Classe::find($student->class_id);
            if ($classe) {
                $this->calculateAllSubjectAverages($classe, $term, $schoolYear);
                $averages = Average::where('student_id', $studentId)
                    ->where('school_year_id', $schoolYearId)
                    ->where('term_id', $termId)
                    ->where('class_id', $student->class_id)
                    ->with('subject')
                    ->get();
            }
        }

        $totalWeightedAverage = 0;
        $totalCoefficients = 0;
        $subjectResults = [];

        foreach ($averages as $average) {
            if ($average->average !== null && $average->subject) {
                $coef = $average->subject->coefficient ?? 1;
                $totalWeightedAverage += $average->average * $coef;
                $totalCoefficients += $coef;

                $subjectResults[] = [
                    'subject' => $average->subject,
                    'average' => $average->average,
                    'appreciation' => $average->appreciation,
                    'coefficient' => $coef
                ];

                Log::debug("Matière: {$average->subject->name}, Moyenne: {$average->average}, Coef: {$coef}");
            }
        }

        Log::info("Total coefficients: {$totalCoefficients}");
        Log::info("Nombre de matières: " . count($subjectResults));

        if ($totalCoefficients == 0) {
            Log::warning("Total coefficients = 0");
            return $this->getEmptyResults();
        }

        $generalAverage = round($totalWeightedAverage / $totalCoefficients, 2);

        // Récupérer ou calculer le rang
        $generalAverageRecord = GeneralAverage::where([
            'student_id' => $studentId,
            'classe_id' => $student->class_id,
            'school_year_id' => $schoolYearId,
            'term_id' => $termId
        ])->first();

        // Si pas de rang, calculer les moyennes générales pour la classe
        if (!$generalAverageRecord) {
            Log::info("Calcul des moyennes générales pour la classe...");
            $classe = Classe::find($student->class_id);
            if ($classe) {
                $this->calculateGeneralAverages($classe, $term, $schoolYear);
                $generalAverageRecord = GeneralAverage::where([
                    'student_id' => $studentId,
                    'classe_id' => $student->class_id,
                    'school_year_id' => $schoolYearId,
                    'term_id' => $termId
                ])->first();
            }
        }

        $results = [
            'general_average' => $generalAverage,
            'subject_results' => $subjectResults,
            'rank' => $generalAverageRecord ? $generalAverageRecord->rank : 0,
            'total_coefficients' => $totalCoefficients,
            'appreciation' => $generalAverageRecord ?
                $generalAverageRecord->appreciation :
                $this->getAppreciation($generalAverage),
            'total_students' => GeneralAverage::where([
                'classe_id' => $student->class_id,
                'school_year_id' => $schoolYearId,
                'term_id' => $termId
            ])->count() ?? 0
        ];

        Log::info("=== FIN CALCUL RÉSULTATS ===", [
            'moyenne_generale' => $results['general_average'],
            'nombre_matières' => count($results['subject_results']),
            'rang' => $results['rank']
        ]);

        return $results;

    } catch (\Exception $e) {
        Log::error("=== ERREUR CALCUL RÉSULTATS ===");
        Log::error("Message: " . $e->getMessage());
        Log::error("Stack trace: " . $e->getTraceAsString());

        return $this->getEmptyResults();
    }
}

private function getEmptyResults()
{
    return [
        'general_average' => 0,
        'subject_results' => [],
        'rank' => 0,
        'total_coefficients' => 0,
        'appreciation' => 'Aucune Note',
        'total_students' => 0
    ];
}

    /**
     * Obtenir le rang d'un étudiant
     */
    private function getStudentRank($studentId, $schoolYearId, $termId)
    {
        $generalAverage = GeneralAverage::where('student_id', $studentId)
            ->where('school_year_id', $schoolYearId)
            ->where('term_id', $termId)
            ->first();

        return $generalAverage ? $generalAverage->rank : 0;
    }

    /**
     * Calculer les statistiques de classe
     */
   public function calculateClassStatistics($classInput = null, $termInput = null, $schoolYearInput = null)
{
    try {
        Log::info('Calcul statistiques classe - Début', [
            'class_input_type' => is_object($classInput) ? get_class($classInput) : gettype($classInput),
            'term_input_type' => is_object($termInput) ? get_class($termInput) : gettype($termInput),
            'school_year_input_type' => is_object($schoolYearInput) ? get_class($schoolYearInput) : gettype($schoolYearInput),
        ]);

        // Validation stricte des paramètres pour détecter les inversions
        if ($classInput instanceof \App\Models\Term || (is_numeric($classInput) && Term::find((int)$classInput))) {
            Log::error('Paramètre "classInput" ressemble à un Term. Vérifier l\'appelant ou l\'ordre des paramètres.', [
                'classInput' => $classInput
            ]);
            throw new \InvalidArgumentException('Le paramètre "classInput" doit être une Classe (ID ou instance).');
        }

        // Résoudre les paramètres dans l'ordre correct
        $class = $this->resolveClassParameter($classInput);
        $term = $this->resolveTermParameter($termInput);
        $schoolYear = $this->resolveSchoolYearParameter($schoolYearInput);

        Log::info('Calcul statistiques classe - Paramètres résolus', [
            'classe' => $class->id,
            'classe_nom' => $class->name,
            'term' => $term->id,
            'term_nom' => $term->name,
            'school_year' => $schoolYear->id,
            'school_year_nom' => $schoolYear->year
        ]);

        $cacheKey = "class_stats_{$class->id}_{$term->id}_{$schoolYear->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($class, $term, $schoolYear) {
            // Obtenir les moyennes générales
            $generalAverages = GeneralAverage::where('classe_id', $class->id)
                ->where('term_id', $term->id)
                ->where('school_year_id', $schoolYear->id)
                ->whereNotNull('average')
                ->get();

            Log::info("Nombre de moyennes générales trouvées: " . $generalAverages->count());

            // Si pas de moyennes, calculer
            if ($generalAverages->isEmpty()) {
                Log::info("Aucune moyenne trouvée, calcul en cours...");
                $this->calculateAllSubjectAverages($class, $term, $schoolYear);
                $this->calculateGeneralAverages($class, $term, $schoolYear);

                $generalAverages = GeneralAverage::where('classe_id', $class->id)
                    ->where('term_id', $term->id)
                    ->where('school_year_id', $schoolYear->id)
                    ->whereNotNull('average')
                    ->get();
            }

            // Si toujours vide, retourner stats vides
            if ($generalAverages->isEmpty()) {
                Log::warning("Aucune moyenne après calcul, retour stats vides");
                return $this->getDefaultStats();
            }

            // Calculer les statistiques
            $averages = $generalAverages->pluck('average')->filter()->toArray();

            if (empty($averages)) {
                Log::warning("Tableau des moyennes vide");
                return $this->getDefaultStats();
            }

            $totalStudents = count($averages);
            $successCount = count(array_filter($averages, fn($avg) => $avg >= 10));
            $classAverage = array_sum($averages) / $totalStudents;
            $successRate = $totalStudents > 0 ? ($successCount / $totalStudents) * 100 : 0;

            $stats = [
                'class_average' => round($classAverage, 2),
                'max_average' => round(max($averages), 2),
                'min_average' => round(min($averages), 2),
                'success_rate' => round($successRate, 2),
                'total_students' => $totalStudents,
                'top_average' => round($generalAverages->max('average'), 2),
                'bottom_average' => round($generalAverages->min('average'), 2),
                'class_id' => $class->id,
                'class_name' => $class->name,
                'term_name' => $term->name,
                'school_year' => $schoolYear->year
            ];

            Log::info('Statistiques calculées', $stats);
            return $stats;
        });

    } catch (\Exception $e) {
        Log::error('Erreur calculateClassStatistics: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return $this->getDefaultStats();
    }
}

private function getDefaultStats()
{
    return [
        'class_average' => 0,
        'max_average' => 0,
        'min_average' => 0,
        'success_rate' => 0,
        'total_students' => 0,
        'top_average' => 0,
        'bottom_average' => 0,
        'class_id' => 0,
        'class_name' => 'Non disponible',
        'term_name' => 'Non disponible',
        'school_year' => 'Non disponible'
    ];
}
    private function getEmptyStats()
    {
        return [
            'class_average' => 0,
            'max_average' => 0,
            'min_average' => 0,
            'success_rate' => 0,
            'total_students' => 0,
            'top_average' => 0,
            'bottom_average' => 0,
        ];
    }
    /**
     * Calculer les statistiques de l'école
     */
    public function calculateSchoolStatistics($class, $term, $schoolYear = null)
    {
        // Vérification et conversion des paramètres
        $term = $this->resolveTermParameter($term);
        $schoolYear = $this->resolveSchoolYearParameter($schoolYear);
        $class = $this->resolveClassParameter($class);

        $cacheKey = "school_stats_{$term->id}_{$schoolYear->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($class, $term, $schoolYear) {
            $classes = Classe::with(['generalAverages' => function($query) use ($class, $term, $schoolYear) {
                $query->where('classe_id', $class->id)
                      ->where('term_id', $term->id)
                      ->where('school_year_id', $schoolYear->id)
                      ->whereNotNull('average');
            }])->get();

            $allAverages = [];
            $classStatistics = [];

            foreach ($classes as $class) {
                $averages = $class->generalAverages->pluck('average')->toArray();
                $allAverages = array_merge($allAverages, $averages);

                if (!empty($averages)) {
                    $classStatistics[$class->name] = [
                        'average' => round(array_sum($averages) / count($averages), 2),
                        'success_rate' => round((count(array_filter($averages, fn($avg) => $avg >= 10)) / count($averages)) * 100, 2),
                        'total_students' => count($averages),
                    ];
                }
            }

            if (empty($allAverages)) {
                return [
                    'school_average' => 0,
                    'success_rate' => 0,
                    'total_students' => 0,
                    'class_statistics' => [],
                    'top_10' => [],
                    'bottom_10' => [],
                ];
            }

            rsort($allAverages);
             $top_10 = array_slice($allAverages, 0, min(10, count($allAverages)));
             $bottom_10 = array_slice($allAverages, -min(10, count($allAverages)));

            return [
                'school_average' => round(array_sum($allAverages) / count($allAverages), 2),
                'success_rate' => round((count(array_filter($allAverages, fn($avg) => $avg >= 10)) / count($allAverages)) * 100, 2),
                'total_students' => count($allAverages),
                'class_statistics' => $classStatistics,
                'top_10' => $top_10,
                'bottom_10' => $bottom_10,
            ];
        });
    }

    /**
     * Recalculer toutes les moyennes
     */
    public function recalculateAllAverages($term, $schoolYear)
    {
        // Vérification et conversion des paramètres
        $term = $this->resolveTermParameter($term);
        $schoolYear = $this->resolveSchoolYearParameter($schoolYear);

        $classes = Classe::active()->get();

        foreach ($classes as $class) {
            $this->calculateAllSubjectAverages($class, $term, $schoolYear);
            $this->calculateGeneralAverages($class, $term, $schoolYear);
        }

        return true;
    }

    /**
     * Obtenir l'appréciation basée sur la moyenne
     */
    private function getAppreciation($average)
    {
        if ($average >= 18) return 'Excellent';
        if ($average >= 16) return 'Très bien';
        if ($average >= 14) return 'Bien';
        if ($average >= 12) return 'Assez bien';
        if ($average >= 10) return 'Passable';
        return 'Insuffisant';
    }

    /**
     * Invalider le cache pour une classe
     */
    private function invalidateClassCache($classId, $termId, $schoolYearId)
    {
        Cache::forget("class_stats_{$classId}_{$termId}_{$schoolYearId}");
        Cache::forget("school_stats_{$termId}_{$schoolYearId}");

        // Invalider également les caches des étudiants
        $students = Student::where('class_id', $classId)->pluck('id');
        foreach ($students as $studentId) {
            Cache::forget("student_results_{$studentId}_{$schoolYearId}_{$termId}");
        }
    }

    /**
     * Résoudre le paramètre classe (peut être un ID ou un objet Classe)
     */
    // Dans MarkCalculationService.php, modifiez la méthode resolveClassParameter :
private function resolveClassParameter($class)
{
    try {
        if ($class instanceof \App\Models\Term) {
            Log::warning("Term passé au lieu de Classe à resolveClassParameter", ['term_id' => $class->id ?? null]);
            throw new \InvalidArgumentException('Term passé au lieu de Classe.');
        }

        if ($class instanceof Classe) {
            return $class;
        }

        if (is_numeric($class)) {
            $found = Classe::find((int)$class);
            if ($found) return $found;
            throw new \InvalidArgumentException("Classe avec ID {$class} non trouvée.");
        }

        if (is_string($class)) {
            $found = Classe::where('name', $class)->orWhere('full_name', $class)->first();
            if ($found) return $found;
            throw new \InvalidArgumentException("Classe '{$class}' non trouvée.");
        }

        if ($class === null) {
            $defaultClass = Classe::active()->first();
            if ($defaultClass) return $defaultClass;
            throw new \InvalidArgumentException('Aucune classe disponible.');
        }

        throw new \InvalidArgumentException('Paramètre classe invalide.');
    } catch (\Exception $e) {
        Log::error("Erreur résolution paramètre classe: " . $e->getMessage(), [
            'param' => $class,
            'type' => is_object($class) ? get_class($class) : gettype($class)
        ]);

        // Fallback contrôlé : retourner null afin que l'appelant puisse gérer l'erreur
        // (ou retourner une classe par défaut si tu veux)
        return null;
    }
}

    /**
     * Résoudre le paramètre term (peut être un ID ou un objet Term)
     */
    // 
    private function resolveTermParameter($term)
{
    try {
        if ($term instanceof Term) return $term;

        if (is_numeric($term) || (is_string($term) && ctype_digit($term))) {
            $found = Term::find((int)$term);
            if ($found) return $found;
            throw new \InvalidArgumentException("Term avec ID {$term} non trouvé.");
        }

        // Cherche le term courant
        $currentTerm = Term::where('is_current', true)->first();
        if ($currentTerm) return $currentTerm;

        // fallback: premier terme existant
        $firstTerm = Term::first();
        if ($firstTerm) return $firstTerm;

        throw new \InvalidArgumentException('Aucun Term disponible.');
    } catch (\Exception $e) {
        Log::error("Erreur resolution du parametre Term: " . $e->getMessage());
        return null;
    }
}

    /**
     * Résoudre le paramètre schoolYear (peut être un ID, un objet SchoolYear ou null)
     */
private function resolveSchoolYearParameter($schoolYear = null)
{
    try {
        if ($schoolYear instanceof SchoolYear) return $schoolYear;

        if (is_numeric($schoolYear) || (is_string($schoolYear) && ctype_digit($schoolYear))) {
            $found = SchoolYear::find((int)$schoolYear);
            if ($found) return $found;
            throw new \InvalidArgumentException("SchoolYear avec ID {$schoolYear} non trouvée.");
        }

        $current = SchoolYear::where('is_current', true)->first();
        if ($current) return $current;

        $first = SchoolYear::first();
        if ($first) return $first;

        // Option: créer l'année scolaire si essentielle (mais attention!)
        $created = SchoolYear::create([
            'year' => now()->year . '-' . (now()->year + 1),
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'is_current' => true
        ]);
        return $created;
    } catch (\Exception $e) {
        Log::error("Erreur resolution du parametre SchoolYear: " . $e->getMessage());
        return null;
    }
}



    private function resolveParameters($class, $term = null, $schoolYear = null)
{
    $class = $this->resolveClassParameter($class);
    $term = $this->resolveTermParameter($term);
    $schoolYear = $this->resolveSchoolYearParameter($schoolYear);

    return compact('class', 'term', 'schoolYear');
}
    /**
     * Nettoyer tous les caches du service
     */
    public function clearAllCache()
    {
        Cache::flush();
    }

    /**
     * Nettoyer le cache pour une classe spécifique
     */
// private function resolveSchoolYearParameter($schoolYear = null)
//     {
//         try{

//         if ($schoolYear instanceof SchoolYear) {
//             return $schoolYear;
//         }

//         if (is_int($schoolYear) || (is_string($schoolYear) && ctype_digit($schoolYear))) {
//             $found = SchoolYear::find((int)$schoolYear);
//             if($fonction clearClassCache($classId, $termId, $schoolYearId)
//     {
//         Cache::forget("class_stats_{$classId}_{$termId}_{$schoolYearId}");

//         // Nettoyer aussi les caches des étudiants
//         $students = Student::where('class_id', $classId)->pluck('id');
//         foreach ($students as $studentId) {
//             Cache::forget("student_results_{$studentId}_{$schoolYearId}_{$termId}");
//         }
//     }


}
