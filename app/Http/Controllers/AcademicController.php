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
use Illuminate\Support\Facades\Auth;

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

        $this->logActivity('create', 'SchoolYear', $schoolYear->id, "Année scolaire '{$schoolYear->year}' créée");

        return redirect()->route('admin.academic.school-years')
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

        $oldData = $schoolYear->toArray();
        $schoolYear->update($validated);

        if ($validated['is_current'] ?? false) {
            $schoolYear->setAsCurrent();
        }

        $this->logActivity('update', 'SchoolYear', $schoolYear->id, "Année scolaire '{$schoolYear->year}' mise à jour");

        return redirect()->route('admin.academic.school-years')
            ->with('success', 'Année scolaire mise à jour avec succès.');
    }

    /**
     * Supprimer une année scolaire
     */
    public function destroySchoolYear(SchoolYear $schoolYear)
    {
        $this->authorize('manage-school-years');

        // Vérifier que l'année scolaire n'est pas l'année courante
        if ($schoolYear->is_current) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer l\'année scolaire actuelle.');
        }

        // Vérifier si l'année contient des données
        if ($schoolYear->terms()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer une année scolaire contenant des trimestres.');
        }

        $yearName = $schoolYear->year;

        try {
            DB::transaction(function () use ($schoolYear) {
                // Supprimer les trimestres associés
                $schoolYear->terms()->delete();

                // Supprimer les évaluations liées
                Evaluation::where('school_year_id', $schoolYear->id)->delete();

                // Supprimer l'année scolaire
                $schoolYear->delete();
            });

            $this->logActivity('delete', 'SchoolYear', $schoolYear->id, "Année scolaire '{$yearName}' supprimée");

            return redirect()->route('admin.academic.school-years')
                ->with('success', "L'année scolaire '{$yearName}' a été supprimée avec succès.");
        } catch (\Exception $e) {
            $this->logActivity('delete_failed', 'SchoolYear', $schoolYear->id, "Erreur lors de la suppression: {$e->getMessage()}");

            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de l\'année scolaire: ' . $e->getMessage());
        }
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
            'order' => 'required|integer|min:1|max:3|unique:terms,order,NULL,id,school_year_id,' . $schoolYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $validated['school_year_id'] = $schoolYear->id;

        $term = Term::create($validated);

        if ($validated['is_current'] ?? false) {
            // Désactiver les autres trimestres de la même année
            $schoolYear->terms()->where('id', '!=', $term->id)->update(['is_current' => false]);
            $term->update(['is_current' => true]);
        }

        $this->logActivity('create', 'Term', $term->id, "Trimestre '{$term->name}' créé pour {$schoolYear->year}");

        return redirect()->route('admin.academic.terms', $schoolYear)
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
            'order' => 'required|integer|min:1|max:3|unique:terms,order,' . $term->id . ',id,school_year_id,' . $term->school_year_id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $term->update($validated);

        if ($validated['is_current'] ?? false) {
            // Désactiver les autres trimestres de la même année
            $term->schoolYear->terms()->where('id', '!=', $term->id)->update(['is_current' => false]);
            $term->update(['is_current' => true]);
        }

        $this->logActivity('update', 'Term', $term->id, "Trimestre '{$term->name}' mis à jour");

        return redirect()->route('admin.academic.terms', $term->schoolYear)
            ->with('success', 'Trimestre mis à jour avec succès.');
    }

    /**
     * Supprimer un trimestre
     */
    public function destroyTerm(Term $term)
    {
        $this->authorize('manage-terms');

        // Vérifier que le trimestre n'est pas le trimestre courant
        if ($term->is_current) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer le trimestre actuel.');
        }

        // Vérifier si le trimestre contient des données
        $evaluationCount = Evaluation::where('term_id', $term->id)->count();
        
        if ($evaluationCount > 0) {
            return redirect()->back()
                ->with('error', "Impossible de supprimer ce trimestre car il contient {$evaluationCount} évaluation(s).");
        }

        $termName = $term->name;
        $schoolYear = $term->schoolYear;

        try {
            DB::transaction(function () use ($term) {
                // Supprimer les évaluations liées
                Evaluation::where('term_id', $term->id)->delete();

                // Supprimer les moyennes associées
                DB::table('averages')->where('term_id', $term->id)->delete();
                DB::table('general_averages')->where('term_id', $term->id)->delete();

                // Supprimer les bulletins
                DB::table('bulletins')->where('term_id', $term->id)->delete();

                // Supprimer le trimestre
                $term->delete();
            });

            $this->logActivity('delete', 'Term', $term->id, "Trimestre '{$termName}' supprimé");

            return redirect()->route('admin.academic.terms', $schoolYear)
                ->with('success', "Le trimestre '{$termName}' a été supprimé avec succès.");
        } catch (\Exception $e) {
            $this->logActivity('delete_failed', 'Term', $term->id, "Erreur lors de la suppression: {$e->getMessage()}");

            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression du trimestre: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete - archiver un trimestre sans le supprimer
     */
    public function archiveTerm(Term $term)
    {
        $this->authorize('manage-terms');

        if ($term->is_current) {
            return redirect()->back()
                ->with('error', 'Impossible d\'archiver le trimestre actuel.');
        }

        $term->update(['is_archived' => true]);

        $this->logActivity('archive', 'Term', $term->id, "Trimestre '{$term->name}' archivé");

        return redirect()->back()
            ->with('success', "Le trimestre '{$term->name}' a été archivé.");
    }

    /**
     * Restaurer un trimestre archivé
     */
    public function restoreTerm(Term $term)
    {
        $this->authorize('manage-terms');

        $term->update(['is_archived' => false]);

        $this->logActivity('restore', 'Term', $term->id, "Trimestre '{$term->name}' restauré");

        return redirect()->back()
            ->with('success', "Le trimestre '{$term->name}' a été restauré.");
    }

    // Méthodes utilitaires
    
    private function calculateSuccessRate()
    {
        $totalStudents = Student::count();
        if ($totalStudents === 0) return 0;

        $passingStudents = Mark::select('student_id')
            ->selectRaw('AVG(marks) as average_mark')
            ->groupBy('student_id')
            ->havingRaw('AVG(marks) >= ?', [10])
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
                    ->selectRaw('AVG(marks) as average_mark')
                    ->groupBy('student_id')
                    ->havingRaw('AVG(marks) >= ?', [10]);
            })
            ->count();

        return ($passingStudents / $studentsCount) * 100;
    }

    private function createDefaultTerms(SchoolYear $schoolYear)
    {
        $startDate = new \DateTime($schoolYear->start_date);
        $endDate = new \DateTime($schoolYear->end_date);

        $terms = [
            [
                'name' => 'Premier Trimestre',
                'order' => 1,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $startDate->modify('+3 months')->format('Y-m-d'),
                'is_current' => false,
            ],
            [
                'name' => 'Deuxième Trimestre',
                'order' => 2,
                'start_date' => $startDate->modify('+1 day')->format('Y-m-d'),
                'end_date' => $startDate->modify('+3 months')->format('Y-m-d'),
                'is_current' => false,
            ],
            [
                'name' => 'Troisième Trimestre',
                'order' => 3,
                'start_date' => $startDate->modify('+1 day')->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'is_current' => false,
            ]
        ];

        foreach ($terms as $termData) {
            $termData['school_year_id'] = $schoolYear->id;
            Term::create($termData);
        }
    }

    /**
     * Enregistrer une activité dans le journal d'audit
     */
    private function logActivity($action, $model, $modelId, $description)
    {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model' => $model,
                'model_id' => $modelId,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silencieusement échouer pour ne pas affecter le flux principal
            \Log::warning('Erreur lors de l\'enregistrement d\'une activité: ' . $e->getMessage());
        }
    }
}
