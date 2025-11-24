<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolYear;
use App\Models\Term;
use App\Models\Classe;
use App\Models\Student;
use App\Models\Evaluation;
use App\Models\Mark;
use App\Services\MarkCalculationService;

class DashboardController extends Controller
{
    protected $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $currentSchoolYear = SchoolYear::current();
        $currentTerm = Term::current();

        if ($user->isAdministrator()) {
            return $this->adminDashboard($currentSchoolYear, $currentTerm);
        } elseif ($user->isDirector()) {
            return $this->directorDashboard($currentSchoolYear, $currentTerm);
        } elseif ($user->isTitularTeacher()) {
            return $this->titularDashboard($user, $currentSchoolYear, $currentTerm);
        } elseif ($user->isTeacher()) {
            return $this->teacherDashboard($user, $currentSchoolYear, $currentTerm);
        } elseif ($user->isSecretary()) {
            return $this->secretaryDashboard($currentSchoolYear, $currentTerm);
        }

        abort(403, 'Rôle non reconnu');
    }

    private function adminDashboard($schoolYear, $term)
    {
        $stats = [
            'total_classes' => Classe::active()->count(),
            'total_students' => Student::where('school_year_id', $schoolYear->id)->count(),
            'total_teachers' => \App\Models\User::role(['enseignant', 'enseignant titulaire'])->active()->count(),
            'total_evaluations' => Evaluation::where('school_year_id', $schoolYear->id)->count(),
        ];

        $schoolStats = $this->calculationService->calculateSchoolStatistics($term, $schoolYear);

        // Évaluations avec notes manquantes
        $evaluationsWithMissingMarks = Evaluation::with(['class', 'subject'])
            ->where('school_year_id', $schoolYear->id)
            ->get()
            ->filter(function($evaluation) {
                return $evaluation->completion_percentage < 100;
            })
            ->take(10);

        return view('dashboard.admin', compact('stats', 'schoolStats', 'evaluationsWithMissingMarks', 'schoolYear', 'term'));
    }

    private function directorDashboard($schoolYear, $term)
    {
        $stats = [
            'total_classes' => Classe::active()->count(),
            'total_students' => Student::where('school_year_id', $schoolYear->id)->count(),
            'total_teachers' => \App\Models\User::role(['enseignant', 'enseignant titulaire'])->active()->count(),
        ];

        $schoolStats = $this->calculationService->calculateSchoolStatistics($term, $schoolYear);
        $classStatistics = [];

        if ($schoolStats) {
            $classStatistics = $schoolStats['class_statistics'];
        }

        // Performances par classe
        $classes = Classe::with(['generalAverages' => function($query) use ($term, $schoolYear) {
            $query->where('term_id', $term->id)
                  ->where('school_year_id', $schoolYear->id);
        }])->get();

        return view('dashboard.director', compact('stats', 'schoolStats', 'classStatistics', 'classes', 'schoolYear', 'term'));
    }

    private function titularDashboard($user, $schoolYear, $term)
    {
        $class = $user->class;

        if (!$class) {
            return view('dashboard.titular-no-class');
        }

        $stats = [
            'total_students' => $class->students()->count(),
            'total_subjects' => $class->teacherAssignments()->distinct('subject_id')->count('subject_id'),
            'completed_evaluations' => Evaluation::where('class_id', $class->id)
                ->where('school_year_id', $schoolYear->id)
                ->where('term_id', $term->id)
                ->get()
                ->filter(fn($eval) => $eval->is_completed)
                ->count(),
        ];

        $classStats = $this->calculationService->calculateClassStatistics($class, $term, $schoolYear);

        // Élèves avec difficultés (moyenne < 10)
        $strugglingStudents = \App\Models\GeneralAverage::with('student')
            ->where('class_id', $class->id)
            ->where('term_id', $term->id)
            ->where('school_year_id', $schoolYear->id)
            ->where('average', '<', 10)
            ->orderBy('average')
            ->get();

        return view('dashboard.titular', compact('stats', 'classStats', 'strugglingStudents', 'class', 'schoolYear', 'term'));
    }

    private function teacherDashboard($user, $schoolYear, $term)
    {
        $assignedClasses = $user->assignedClasses()->with('students')->get();
        $assignedSubjects = $user->assignedSubjects;

        $stats = [
            'total_classes' => $assignedClasses->count(),
            'total_students' => $assignedClasses->sum(fn($class) => $class->students->count()),
            'total_subjects' => $assignedSubjects->count(),
        ];

        // Évaluations à compléter
        $pendingEvaluations = Evaluation::whereIn('class_id', $assignedClasses->pluck('id'))
            ->whereIn('subject_id', $assignedSubjects->pluck('id'))
            ->where('school_year_id', $schoolYear->id)
            ->where('term_id', $term->id)
            ->get()
            ->filter(function($evaluation) {
                return $evaluation->completion_percentage < 100;
            });

        return view('dashboard.teacher', compact('stats', 'assignedClasses', 'assignedSubjects', 'pendingEvaluations', 'schoolYear', 'term'));
    }

    private function secretaryDashboard($schoolYear, $term)
    {
        $stats = [
            'total_students' => Student::where('school_year_id', $schoolYear->id)->count(),
            'total_classes' => Classe::active()->count(),
            'new_students_this_year' => Student::where('school_year_id', $schoolYear->id)
                ->whereDate('created_at', '>=', $schoolYear->start_date)
                ->count(),
        ];

        // Documents récents à générer
        $recentEvaluations = Evaluation::with(['class', 'subject'])
            ->where('school_year_id', $schoolYear->id)
            ->where('term_id', $term->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.secretary', compact('stats', 'recentEvaluations', 'schoolYear', 'term'));
    }
}
