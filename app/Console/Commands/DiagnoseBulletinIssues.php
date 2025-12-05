<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Classe;
use App\Models\Evaluation;
use App\Models\Mark;
use App\Models\Average;
use App\Models\GeneralAverage;

class DiagnoseBulletinIssues extends Command
{
    protected $signature = 'diagnose:bulletin-issues {class?}';
    protected $description = 'Diagnostiquer les problèmes de génération de bulletins';

    public function handle()
    {
        $classId = $this->argument('class');

        $this->info('=== DIAGNOSTIC DES PROBLÈMES DE BULLETINS ===');

        // Vérifier les étudiants sans classe
        $studentsWithoutClass = Student::whereNull('class_id')->count();
        $this->info("Étudiants sans classe: {$studentsWithoutClass}");

        // Vérifier les classes sans étudiants
        $classesWithoutStudents = Classe::doesntHave('students')->count();
        $this->info("Classes sans étudiants: {$classesWithoutStudents}");

        if ($classId) {
            $classe = Classe::find($classId);
            if ($classe) {
                $this->analyzeClass($classe);
            }
        } else {
            // Analyser quelques classes
            $classes = Classe::withCount(['students', 'evaluations'])->take(5)->get();
            foreach ($classes as $classe) {
                $this->analyzeClass($classe);
            }
        }

        $this->info('=== DIAGNOSTIC TERMINÉ ===');
        return 0;
    }

    private function analyzeClass(Classe $classe)
    {
        $this->info("\n--- Analyse de la classe: {$classe->name} ---");
        
        $this->info("Nombre d'étudiants: {$classe->students_count}");
        $this->info("Nombre d'évaluations: {$classe->evaluations_count}");

        // Vérifier les notes
        $marksCount = Mark::whereIn('student_id', $classe->students->pluck('id'))->count();
        $this->info("Nombre total de notes: {$marksCount}");

        // Vérifier les moyennes
        $averagesCount = Average::whereIn('student_id', $classe->students->pluck('id'))->count();
        $this->info("Nombre de moyennes par matière: {$averagesCount}");

        $generalAveragesCount = GeneralAverage::whereIn('student_id', $classe->students->pluck('id'))->count();
        $this->info("Nombre de moyennes générales: {$generalAveragesCount}");

        // Étudiants sans notes
        $studentsWithoutMarks = $classe->students->filter(function($student) {
            return $student->marks->isEmpty();
        })->count();
        $this->info("Étudiants sans aucune note: {$studentsWithoutMarks}");

        if ($studentsWithoutMarks > 0) {
            $this->warn("⚠️  Certains étudiants n'ont pas de notes!");
        }
    }
}