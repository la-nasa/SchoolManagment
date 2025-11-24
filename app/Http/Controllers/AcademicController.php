<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Term;
use App\Models\Student;
use App\Models\Evaluation;
use App\Models\Mark;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function schoolYears()
    {
        $this->authorize('manage-school-years');

        $schoolYears = SchoolYear::with('terms')->orderBy('start_date', 'desc')->get();

        return view('academic.school-year', compact('schoolYears'));
    }

    public function dashboard()
    {
        $this->authorize('view-dashboard');

        // Récupérer l'année scolaire actuelle
        $currentSchoolYear = SchoolYear::current();
        
        // Statistiques de base
        $stats = [
            'total_students' => Student::count(),
            'success_rate' => $this->calculateSuccessRate(),
            'average_mark' => $this->calculateAverageMark(),
            'total_evaluations' => Evaluation::count(),
        ];

        // Performance des classes
        $classPerformance = Classe::withCount('students')
            ->with(['marks' => function($query) {
                $query->select('class_id', DB::raw('AVG(mark) as average_mark'));
            }])
            ->get()
            ->map(function($class) {
                $averageMark = $class->marks->avg('average_mark') ?? 0;
                $successRate = $this->calculateClassSuccessRate($class);
                
                return (object)[
                    'name' => $class->name,
                    'students_count' => $class->students_count,
                    'average_mark' => $averageMark,
                    'success_rate' => $successRate
                ];
            });

        // Performance par matière
        $subjectPerformance = Subject::with(['marks' => function($query) {
                $query->select('subject_id', DB::raw('AVG(mark) as average_mark'));
            }])
            ->withCount('evaluations')
            ->get()
            ->map(function($subject) {
                $averageMark = $subject->marks->avg('average_mark') ?? 0;
                
                return (object)[
                    'name' => $subject->name,
                    'coefficient' => $subject->coefficient,
                    'average_mark' => $averageMark,
                    'evaluations_count' => $subject->evaluations_count
                ];
            });

        // Activités récentes
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('academic.dashboard', compact(
            'stats', 
            'classPerformance', 
            'subjectPerformance', 
            'recentActivities',
            'currentSchoolYear'
        ));
    }

    private function calculateSuccessRate()
    {
        // Logique pour calculer le taux de réussite
        $totalStudents = Student::count();
        if ($totalStudents === 0) return 0;

        $passingStudents = Mark::select('student_id')
            ->selectRaw('AVG(mark) as average_mark')
            ->groupBy('student_id')
            ->havingRaw('AVG(mark) >= ?', [10])
            ->count();

        return ($passingStudents / $totalStudents) * 100;
    }

    private function calculateAverageMark()
    {
        return Mark::avg('marks') ?? 0;
    }

    private function calculateClassSuccessRate($class)
    {
        $studentsCount = $class->students_count;
        if ($studentsCount === 0) return 0;

        $passingStudents = $class->students()
            ->whereHas('marks', function($query) {
                $query->select('student_id')
                    ->selectRaw('AVG(mark) as average_mark')
                    ->groupBy('student_id')
                    ->havingRaw('AVG(mark) >= ?', [10]);
            })
            ->count();

        return ($passingStudents / $studentsCount) * 100;
    }

    public function createSchoolYear()
    {
        $this->authorize('manage-school-years');

        return view('academic.create-school-year');
    }

    public function storeSchoolYear(Request $request)
    {
        $this->authorize('manage-school-years');

        $validated = $request->validate([
            'year' => 'required|string|max:9|unique:school_years',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $schoolYear = SchoolYear::create($validated);

        // Créer les trimestres par défaut
        $this->createDefaultTerms($schoolYear);

        if ($validated['is_current'] ?? false) {
            $schoolYear->setAsCurrent();
        }

        return redirect()->route('academic.school-years')
            ->with('success', 'Année scolaire créée avec succès.');
    }

    public function editSchoolYear(SchoolYear $schoolYear)
    {
        $this->authorize('manage-school-years');

        return view('academic.edit-school-year', compact('schoolYear'));
    }

    public function updateSchoolYear(Request $request, SchoolYear $schoolYear)
    {
        $this->authorize('manage-school-years');

        $validated = $request->validate([
            'year' => 'required|string|max:9|unique:school_years,year,' . $schoolYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $schoolYear->update($validated);

        if ($validated['is_current'] ?? false) {
            $schoolYear->setAsCurrent();
        }

        return redirect()->route('academic.school-years')
            ->with('success', 'Année scolaire mise à jour avec succès.');
    }

    public function terms(SchoolYear $schoolYear)
    {
        $this->authorize('manage-terms');

        $terms = $schoolYear->terms()->orderBy('order')->get();

        return view('academic.terms', compact('schoolYear', 'terms'));
    }

    public function createTerm(SchoolYear $schoolYear)
    {
        $this->authorize('manage-terms');

        return view('academic.create-term', compact('schoolYear'));
    }

    public function storeTerm(Request $request, SchoolYear $schoolYear)
    {
        $this->authorize('manage-terms');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'required|integer|min:1|max:3',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $validated['school_year_id'] = $schoolYear->id;

        $term = Term::create($validated);

        if ($validated['is_current'] ?? false) {
            $term->setAsCurrent();
        }

        return redirect()->route('academic.terms', $schoolYear)
            ->with('success', 'Trimestre créé avec succès.');
    }

    public function editTerm(Term $term)
    {
        $this->authorize('manage-terms');

        return view('academic.edit-term', compact('term'));
    }

    public function updateTerm(Request $request, Term $term)
    {
        $this->authorize('manage-terms');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'required|integer|min:1|max:3',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $term->update($validated);

        if ($validated['is_current'] ?? false) {
            $term->setAsCurrent();
        }

        return redirect()->route('academic.terms', $term->schoolYear)
            ->with('success', 'Trimestre mis à jour avec succès.');
    }

    private function createDefaultTerms(SchoolYear $schoolYear)
    {
        $terms = [
            [
                'name' => 'Premier Trimestre',
                'order' => 1,
                'start_date' => $schoolYear->start_date,
                'end_date' => date('Y-12-20', strtotime($schoolYear->start_date)),
                'is_current' => false,
            ],
            [
                'name' => 'Deuxième Trimestre',
                'order' => 2,
                'start_date' => date('Y-01-08', strtotime($schoolYear->end_date)),
                'end_date' => date('Y-04-10', strtotime($schoolYear->end_date)),
                'is_current' => false,
            ],
            [
                'name' => 'Troisième Trimestre',
                'order' => 3,
                'start_date' => date('Y-04-20', strtotime($schoolYear->end_date)),
                'end_date' => $schoolYear->end_date,
                'is_current' => false,
            ]
        ];

        foreach ($terms as $termData) {
            $termData['school_year_id'] = $schoolYear->id;
            Term::create($termData);
        }
    }
}