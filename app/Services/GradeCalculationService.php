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

class GradeCalculationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function calculateSubjectAverage(Student $student, Subject $subject, Term $term, SchoolYear $schoolYear)
    {
        // Récupérer toutes les évaluations pour cette matière, classe et trimestre
        $evaluations = Evaluation::where('class_id', $student->class_id)
            ->where('subject_id', $subject->id)
            ->where('term_id', $term->id)
            ->where('school_year_id', $schoolYear->id)
            ->with('examType')
            ->get();

        $totalWeightedMarks = 0;
        $totalWeights = 0;
        $hasMarks = false;

        foreach ($evaluations as $evaluation) {
            $mark = Mark::where('student_id', $student->id)
                ->where('evaluation_id', $evaluation->id)
                ->first();

            if ($mark && !$mark->is_absent) {
                $weight = $evaluation->examType->weight;
                $normalizedMark = ($mark->marks / $evaluation->max_marks) * 20;

                $totalWeightedMarks += $normalizedMark * $weight;
                $totalWeights += $weight;
                $hasMarks = true;
            }
        }

        if (!$hasMarks || $totalWeights === 0) {
            return null;
        }

        $average = $totalWeightedMarks / $totalWeights;
        return round($average, 2);
    }

    /**
     * Calculer toutes les moyennes par matière pour une classe et un trimestre
     */
    public function calculateAllSubjectAverages(Classe $class, Term $term, SchoolYear $schoolYear)
    {
        $students = $class->students;
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

        return true;
    }

    /**
     * Calculer les moyennes générales pour une classe et un trimestre
     */
    public function calculateGeneralAverages(Classe $class, Term $term, SchoolYear $schoolYear)
    {
        $students = $class->students;
        $generalAverages = [];

        DB::transaction(function () use ($students, $class, $term, $schoolYear, &$generalAverages) {
            foreach ($students as $student) {
                $averages = Average::where('student_id', $student->id)
                    ->where('class_id', $class->id)
                    ->where('term_id', $term->id)
                    ->where('school_year_id', $schoolYear->id)
                    ->with('subject')
                    ->get();

                $totalWeightedAverage = 0;
                $totalCoefficients = 0;
                $hasAverages = false;

                foreach ($averages as $average) {
                    if ($average->average !== null) {
                        $totalWeightedAverage += $average->average * $average->subject->coefficient;
                        $totalCoefficients += $average->subject->coefficient;
                        $hasAverages = true;
                    }
                }

                if ($hasAverages && $totalCoefficients > 0) {
                    $generalAverage = $totalWeightedAverage / $totalCoefficients;
                    $generalAverages[$student->id] = round($generalAverage, 2);
                } else {
                    $generalAverages[$student->id] = null;
                }
            }

            // Classer les élèves
            $rankedAverages = $generalAverages;
            arsort($rankedAverages);
            $rank = 1;

            foreach ($rankedAverages as $studentId => $average) {
                if ($average !== null) {
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
                            'total_students' => count(array_filter($generalAverages)),
                        ]
                    );
                    $rank++;
                }
            }
        });

        return $generalAverages;
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
     * Calculer les statistiques de classe
     */
    public function calculateClassStatistics(Classe $class, Term $term, SchoolYear $schoolYear)
    {
        $generalAverages = GeneralAverage::where('class_id', $class->id)
            ->where('term_id', $term->id)
            ->where('school_year_id', $schoolYear->id)
            ->whereNotNull('average')
            ->get();

        if ($generalAverages->isEmpty()) {
            return null;
        }

        $averages = $generalAverages->pluck('average')->toArray();

        return [
            'class_average' => round(array_sum($averages) / count($averages), 2),
            'max_average' => max($averages),
            'min_average' => min($averages),
            'success_rate' => round((count(array_filter($averages, fn($avg) => $avg >= 10)) / count($averages)) * 100, 2),
            'total_students' => count($averages),
            'top_10' => array_slice($averages, 0, min(10, count($averages))),
            'bottom_10' => array_slice($averages, -min(10, count($averages))),
        ];
    }

    /**
     * Calculer les statistiques de l'établissement
     */
    public function calculateSchoolStatistics(Term $term, SchoolYear $schoolYear)
    {
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
            return null;
        }

        rsort($allAverages);

        return [
            'school_average' => round(array_sum($allAverages) / count($allAverages), 2),
            'success_rate' => round((count(array_filter($allAverages, fn($avg) => $avg >= 10)) / count($allAverages)) * 100, 2),
            'total_students' => count($allAverages),
            'top_10' => array_slice($allAverages, 0, min(10, count($allAverages))),
            'bottom_10' => array_slice($allAverages, -min(10, count($allAverages))),
            'class_statistics' => $classStatistics,
        ];
    }

    /**
     * Vérifier les notes manquantes pour une évaluation
     */
    public function getMissingMarks(Evaluation $evaluation)
    {
        $markedStudents = $evaluation->marks()->pluck('student_id');
        $allStudents = $evaluation->class->students()->pluck('id');

        return $allStudents->diff($markedStudents);
    }

    /**
     * Recalculer toutes les moyennes pour un trimestre
     */
    public function recalculateAllAverages(Term $term, SchoolYear $schoolYear)
    {
        $classes = Classe::active()->get();

        foreach ($classes as $class) {
            $this->calculateAllSubjectAverages($class, $term, $schoolYear);
            $this->calculateGeneralAverages($class, $term, $schoolYear);
        }

        return true;
    }
}
