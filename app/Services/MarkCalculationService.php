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
use Illuminate\Support\Facades\Cache;

class MarkCalculationService
{
    const CACHE_TTL = 3600; // 1 heure

    /**
     * Calculer la moyenne d'un élève pour une matière et un trimestre
     */
    public function calculateSubjectAverage(Student $student, Subject $subject, Term $term, SchoolYear $schoolYear)
    {
        $cacheKey = "subject_avg_{$student->id}_{$subject->id}_{$term->id}_{$schoolYear->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($student, $subject, $term, $schoolYear) {
            $evaluations = Evaluation::where('class_id', $student->class_id)
                ->where('subject_id', $subject->id)
                ->where('term_id', $term->id)
                ->where('school_year_id', $schoolYear->id)
                ->with('examType')
                ->get();

            if ($evaluations->isEmpty()) {
                return null;
            }

            $totalWeightedMarks = 0;
            $totalWeight = 0;

            foreach ($evaluations as $evaluation) {
                $mark = Mark::where('student_id', $student->id)
                    ->where('evaluation_id', $evaluation->id)
                    ->first();

                if ($mark && !$mark->is_absent) {
                    $weight = $evaluation->examType->weight ?? 1;
                    $totalWeightedMarks += $mark->marks * $weight;
                    $totalWeight += $weight;
                }
            }

            if ($totalWeight == 0) {
                return null;
            }

            return round($totalWeightedMarks / $totalWeight, 2);
        });
    }

    /**
     * Calculer toutes les moyennes par matière pour une classe
     */
    public function calculateAllSubjectAverages(Classe $class, Term $term, SchoolYear $schoolYear)
    {
        $students = $class->students()->get();
        $subjects = Subject::active()->get();

        DB::transaction(function () use ($students, $subjects, $class, $term, $schoolYear) {
            foreach ($students as $student) {
                foreach ($subjects as $subject) {
                    $average = $this->calculateSubjectAverage($student, $subject, $term, $schoolYear);

                    if ($average !== null) {
                        Average::updateOrCreate(
                            [
                                'student_id' => $student->id,
                                'subject_id' => $subject->id,
                                'class_id' => $class->id,
                                'term_id' => $term->id,
                                'school_year_id' => $schoolYear->id,
                            ],
                            [
                                'average' => $average,
                                'appreciation' => $this->getAppreciation($average),
                            ]
                        );
                    }
                }
            }
        });

        // Invalider le cache
        $this->invalidateClassCache($class->id, $term->id, $schoolYear->id);

        return true;
    }

    /**
     * Calculer les moyennes générales pour une classe
     */
    public function calculateGeneralAverages(Classe $class, Term $term, SchoolYear $schoolYear)
    {
        $students = $class->students()->get();

        DB::transaction(function () use ($students, $class, $term, $schoolYear) {
            $allAverages = [];

            foreach ($students as $student) {
                $averages = Average::where('student_id', $student->id)
                    ->where('class_id', $class->id)
                    ->where('term_id', $term->id)
                    ->where('school_year_id', $schoolYear->id)
                    ->with('subject')
                    ->get();

                $totalWeightedAverage = 0;
                $totalCoefficients = 0;

                foreach ($averages as $average) {
                    if ($average->average !== null) {
                        $totalWeightedAverage += $average->average * $average->subject->coefficient;
                        $totalCoefficients += $average->subject->coefficient;
                    }
                }

                if ($totalCoefficients > 0) {
                    $generalAverage = round($totalWeightedAverage / $totalCoefficients, 2);
                    $allAverages[$student->id] = $generalAverage;
                }
            }

            // Classer les élèves
            arsort($allAverages);
            $rank = 1;

            foreach ($allAverages as $studentId => $average) {
                GeneralAverage::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_id' => $class->id,
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
        });

        $this->invalidateClassCache($class->id, $term->id, $schoolYear->id);
        return true;
    }

    /**
     * Obtenir les résultats d'un étudiant
     */
    public function calculateStudentResults($studentId, $schoolYearId, $termId)
    {
        $cacheKey = "student_results_{$studentId}_{$schoolYearId}_{$termId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($studentId, $schoolYearId, $termId) {
            $student = Student::findOrFail($studentId);
            $averages = Average::where('student_id', $studentId)
                ->where('school_year_id', $schoolYearId)
                ->where('term_id', $termId)
                ->with('subject')
                ->get();

            $totalWeightedAverage = 0;
            $totalCoefficients = 0;
            $subjectResults = [];

            foreach ($averages as $average) {
                if ($average->average !== null) {
                    $totalWeightedAverage += $average->average * $average->subject->coefficient;
                    $totalCoefficients += $average->subject->coefficient;
                    $subjectResults[] = [
                        'subject' => $average->subject,
                        'average' => $average->average,
                        'appreciation' => $average->appreciation
                    ];
                }
            }

            $generalAverage = $totalCoefficients > 0 ? round($totalWeightedAverage / $totalCoefficients, 2) : 0;

            return [
                'general_average' => $generalAverage,
                'subject_results' => $subjectResults,
                'rank' => $this->getStudentRank($studentId, $schoolYearId, $termId),
                'total_coefficients' => $totalCoefficients
            ];
        });
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
    public function calculateClassStatistics($class, $term, $schoolYear)
    {
        // Vérification et conversion des paramètres
        $class = $this->resolveClassParameter($class);
        $term = $this->resolveTermParameter($term);
        $schoolYear = $this->resolveSchoolYearParameter($schoolYear);

        $cacheKey = "class_stats_{$class->id}_{$term->id}_{$schoolYear->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($class, $term, $schoolYear) {
            $generalAverages = GeneralAverage::where('classe_id', $class->id)
                ->where('term_id', $term->id)
                ->where('school_year_id', $schoolYear->id)
                ->whereNotNull('average')
                ->get();

            if ($generalAverages->isEmpty()) {
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

            $averages = $generalAverages->pluck('average')->toArray();

            return [
                'class_average' => round(array_sum($averages) / count($averages), 2),
                'max_average' => max($averages),
                'min_average' => min($averages),
                'success_rate' => round((count(array_filter($averages, fn($avg) => $avg >= 10)) / count($averages)) * 100, 2),
                'total_students' => count($averages),
                'top_average' => $averages[0] ?? 0,
                'bottom_average' => end($averages) ?: 0,
            ];
        });
    }

    /**
     * Calculer les statistiques de l'école
     */
    public function calculateSchoolStatistics($term, $schoolYear = null)
    {
        // Vérification et conversion des paramètres
        $term = $this->resolveTermParameter($term);
        $schoolYear = $this->resolveSchoolYearParameter($schoolYear);

        $cacheKey = "school_stats_{$term->id}_{$schoolYear->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($term, $schoolYear) {
            $classes = Classe::with(['generalAverages' => function($query) use ($term, $schoolYear) {
                $query->where('term_id', $term->id)
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
        if ($average >= 16) return 'Excellent';
        if ($average >= 14) return 'Très bien';
        if ($average >= 12) return 'Bien';
        if ($average >= 10) return 'Assez bien';
        if ($average >= 8) return 'Passable';
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
    private function resolveClassParameter($class)
    {
        if ($class instanceof Classe) {
            return $class;
        }

        if (is_int($class)) {
            return Classe::findOrFail($class);
        }

        throw new \InvalidArgumentException('Le paramètre class doit être une instance de Classe ou un ID entier');
    }

    /**
     * Résoudre le paramètre term (peut être un ID ou un objet Term)
     */
    private function resolveTermParameter($term)
    {
        if ($term instanceof Term) {
            return $term;
        }

        if (is_int($term)) {
            return Term::findOrFail($term);
        }

        throw new \InvalidArgumentException('Le paramètre term doit être une instance de Term ou un ID entier');
    }

    /**
     * Résoudre le paramètre schoolYear (peut être un ID, un objet SchoolYear ou null)
     */
    private function resolveSchoolYearParameter($schoolYear = null)
    {
        if ($schoolYear instanceof SchoolYear) {
            return $schoolYear;
        }

        if (is_int($schoolYear)) {
            return SchoolYear::findOrFail($schoolYear);
        }

        if ($schoolYear === null) {
            return SchoolYear::current();
        }

        throw new \InvalidArgumentException('Le paramètre schoolYear doit être une instance de SchoolYear, un ID entier ou null');
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
    public function clearClassCache($classId, $termId = null, $schoolYearId = null)
    {
        if ($termId && $schoolYearId) {
            $this->invalidateClassCache($classId, $termId, $schoolYearId);
        } else {
            // Si pas de term/schoolYear spécifiés, nettoyer tous les caches possibles
            $terms = Term::all();
            $schoolYears = SchoolYear::all();

            foreach ($terms as $term) {
                foreach ($schoolYears as $schoolYear) {
                    $this->invalidateClassCache($classId, $term->id, $schoolYear->id);
                }
            }
        }
    }


}
