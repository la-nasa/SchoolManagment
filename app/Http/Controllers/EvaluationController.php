<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Evaluation;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\ExamType;
use App\Models\Term;
use App\Models\SchoolYear;
use App\Models\ActivityLog;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Lister toutes les évaluations
     */
    public function index(Request $request)
    {
        $this->authorize('view-evaluations');

        try {
            $query = Evaluation::with(['class', 'subject', 'examType', 'term']);

            $user = $request->user();

            // Appliquer les filtres selon le rôle
            if ($user->hasRole(['enseignant', 'enseignant titulaire'])) {
                $assignedClassIds = $user->teacherAssignments()->pluck('class_id');
                $assignedSubjectIds = $user->teacherAssignments()->pluck('subject_id');

                if ($assignedClassIds->isEmpty() || $assignedSubjectIds->isEmpty()) {
                    return view('evaluations.index', [
                        'evaluations' => collect(),
                        'classes' => Classe::active()->get(),
                        'subjects' => Subject::active()->get(),
                        'terms' => Term::all(),
                        'currentSchoolYear' => SchoolYear::current(),
                        'message' => 'Vous n\'êtes assigné à aucune classe.'
                    ]);
                }

                $query->whereIn('class_id', $assignedClassIds)
                      ->whereIn('subject_id', $assignedSubjectIds);
            }

            // Filtres additionnels
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            if ($request->filled('term_id')) {
                $query->where('term_id', $request->term_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('title', 'like', "%{$search}%");
            }

            $evaluations = $query->orderBy('exam_date', 'desc')->paginate(20);

            $classes = Classe::active()->get();
            $subjects = Subject::active()->get();
            $terms = Term::all();
            $currentSchoolYear = SchoolYear::current();

            return view('evaluations.index', compact('evaluations', 'classes', 'subjects', 'terms', 'currentSchoolYear'));

        } catch (\Exception $e) {
            Log::error('Erreur dans EvaluationController::index - ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors du chargement des évaluations.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $this->authorize('create-evaluations');

        try {
            $classes = Classe::active()->get();
            $subjects = Subject::active()->get();
            $examTypes = ExamType::all();
            $terms = Term::all();
            $schoolYears = SchoolYear::all();
            $currentSchoolYear = SchoolYear::current();
            $teachers = User::role(['enseignant', 'enseignant titulaire'])
                ->where('is_active', true)
                ->with('teacherAssignments')
                ->get();

            if ($classes->isEmpty() || $subjects->isEmpty() || $examTypes->isEmpty()) {
                return redirect()->back()
                    ->with('warning', 'Veuillez d\'abord créer des classes, matières et types d\'examen.');
            }

            return view('evaluations.create', compact('classes', 'subjects', 'examTypes', 'terms', 'schoolYears', 'currentSchoolYear', 'teachers'));

        } catch (\Exception $e) {
            Log::error('Erreur dans EvaluationController::create - ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.');
        }
    }

    /**
     * Enregistrer une évaluation
     */
    public function store(Request $request)
    {
        $this->authorize('create-evaluations');

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'exam_date' => 'required|date',
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'exam_type_id' => 'required|exists:exam_types,id',
                'term_id' => 'required|exists:terms,id',
                'school_year_id' => 'required|exists:school_years,id',
                'max_marks' => 'required|numeric|min:1|max:100',
                'pass_marks' => 'required|numeric|min:0|max:100|lte:max_marks',
                'description' => 'nullable|string|max:1000',
            ]);

            $user = $request->user();

            // Vérifier les permissions pour les enseignants
            if ($user->hasRole(['enseignant', 'enseignant titulaire'])) {
                $isAssigned = TeacherAssignment::where('teacher_id', $user->id)
                    ->where('class_id', $validated['class_id'])
                    ->where('subject_id', $validated['subject_id'])
                    ->exists();

                if (!$isAssigned) {
                    return redirect()->back()
                        ->with('error', 'Vous n\'êtes pas assigné à cette classe/matière.')
                        ->withInput();
                }
                $validated['created_by'] = $user->id;
            } else {
                // Pour les administrateurs, chercher l'enseignant assigné
                $assignment = TeacherAssignment::where('class_id', $validated['class_id'])
                    ->where('subject_id', $validated['subject_id'])
                    ->first();

                $validated['created_by'] = $assignment?->teacher_id ?? Auth::id();
            }

            $evaluation = Evaluation::create($validated);

            $this->logActivity(
                'create',
                'Evaluation',
                $evaluation->id,
                "Évaluation '{$evaluation->title}' créée pour la classe et matière spécifiées"
            );

            return redirect()->route('admin.evaluations.show', $evaluation)
                ->with('success', 'Évaluation créée avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation échouée pour la création d\'évaluation', $e->errors());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Vérifiez les informations saisies.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création d\'une évaluation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la création.')
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'une évaluation
     */
    public function show(Evaluation $evaluation)
    {
        $this->authorize('view-evaluations');

        try {
            if (!$evaluation) {
                return redirect()->route('admin.evaluations.index')
                    ->with('error', 'L\'évaluation spécifiée n\'existe pas.');
            }

            $this->checkTeacherPermissions($evaluation);

            $evaluation->load(['class.teacher', 'subject', 'examType', 'term', 'schoolYear', 'marks.student']);

            $completionPercentage = $this->getCompletionPercentage($evaluation);
            $missingMarks = $evaluation->class->students()
                ->whereNotIn('id', $evaluation->marks->pluck('student_id'))
                ->get();

            return view('evaluations.show', compact('evaluation', 'completionPercentage', 'missingMarks'));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->back()
                ->with('error', 'Vous n\'êtes pas autorisé à voir cette évaluation.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'évaluation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Evaluation $evaluation)
    {
        $this->authorize('edit-evaluations');

        try {
            if (!$evaluation) {
                return redirect()->route('admin.evaluations.index')
                    ->with('error', 'L\'évaluation n\'existe pas.');
            }

            $this->checkTeacherPermissions($evaluation);

            $classes = Classe::active()->get();
            $subjects = Subject::active()->get();
            $examTypes = ExamType::all();
            $terms = Term::all();
            $schoolYears = SchoolYear::all();
            $teachers = User::role(['enseignant', 'enseignant titulaire'])
                ->where('is_active', true)
                ->get();

            return view('evaluations.edit', compact('evaluation', 'classes', 'subjects', 'examTypes', 'terms', 'schoolYears', 'teachers'));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->back()
                ->with('error', 'Vous n\'êtes pas autorisé à modifier cette évaluation.');
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire d\'édition: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.');
        }
    }

    /**
     * Mettre à jour une évaluation
     */
    public function update(Request $request, Evaluation $evaluation)
    {
        $this->authorize('edit-evaluations');

        try {
            if (!$evaluation) {
                return redirect()->route('admin.evaluations.index')
                    ->with('error', 'L\'évaluation n\'existe pas.');
            }

            $this->checkTeacherPermissions($evaluation);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'exam_date' => 'required|date',
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'exam_type_id' => 'required|exists:exam_types,id',
                'term_id' => 'required|exists:terms,id',
                'school_year_id' => 'required|exists:school_years,id',
                'max_marks' => 'required|numeric|min:1|max:100',
                'pass_marks' => 'required|numeric|min:0|max:100|lte:max_marks',
                'description' => 'nullable|string|max:1000',
            ]);

            $oldData = $evaluation->only(['title', 'exam_date', 'max_marks', 'pass_marks']);
            $evaluation->update($validated);

            $this->logActivity(
                'update',
                'Evaluation',
                $evaluation->id,
                "Évaluation '{$evaluation->title}' mise à jour",
                $oldData,
                $validated
            );

            return redirect()->route('admin.evaluations.show', $evaluation)
                ->with('success', 'Évaluation mise à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Vérifiez les informations saisies.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'évaluation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour.')
                ->withInput();
        }
    }

    /**
     * Supprimer une évaluation
     */
    public function destroy(Evaluation $evaluation)
    {
        $this->authorize('delete-evaluations');

        try {
            if (!$evaluation) {
                return redirect()->route('admin.evaluations.index')
                    ->with('error', 'L\'évaluation n\'existe pas.');
            }

            $this->checkTeacherPermissions($evaluation);

            $marksCount = $evaluation->marks()->count();
            $evaluationTitle = $evaluation->title;
            $evaluationId = $evaluation->id;

            DB::transaction(function () use ($evaluation) {
                // Supprimer les notes associées
                $evaluation->marks()->delete();
                // Supprimer l'évaluation
                $evaluation->delete();
            });

            $this->logActivity(
                'delete',
                'Evaluation',
                $evaluationId,
                "Évaluation '{$evaluationTitle}' supprimée ({$marksCount} note(s) supprimée(s))"
            );

            return redirect()->route('admin.evaluations.index')
                ->with('success', "L'évaluation '{$evaluationTitle}' a été supprimée avec succès.");

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->back()
                ->with('error', 'Vous n\'êtes pas autorisé à supprimer cette évaluation.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'évaluation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    /**
     * Afficher les notes d'une évaluation
     */
    public function showMarks(Evaluation $evaluation)
    {
        $this->authorize('view-evaluations');

        try {
            if (!$evaluation) {
                return redirect()->route('admin.evaluations.index')
                    ->with('error', 'L\'évaluation n\'existe pas.');
            }

            $this->checkTeacherPermissions($evaluation);

            $evaluation->load(['class.students.user', 'marks.student']);

            $students = $evaluation->class->students()->with(['marks' => function($query) use ($evaluation) {
                $query->where('evaluation_id', $evaluation->id);
            }])->get();

            return view('evaluations.marks', compact('evaluation', 'students'));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->back()
                ->with('error', 'Vous n\'êtes pas autorisé à voir ces notes.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des notes: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.');
        }
    }

    /**
     * Méthode pour les enseignants - Voir ses évaluations
     */
    public function teacherEvaluations(Request $request)
    {
        $user = $request->user();

        try {
            $assignedClassIds = $user->teacherAssignments()->pluck('class_id');
            $assignedSubjectIds = $user->teacherAssignments()->pluck('subject_id');

            if ($assignedClassIds->isEmpty() || $assignedSubjectIds->isEmpty()) {
                return view('evaluations.teacher-index', [
                    'evaluations' => collect(),
                    'currentSchoolYear' => SchoolYear::current(),
                    'message' => 'Vous n\'êtes assigné à aucune classe.'
                ]);
            }

            $evaluations = Evaluation::with(['class', 'subject', 'examType', 'term'])
                ->whereIn('class_id', $assignedClassIds)
                ->whereIn('subject_id', $assignedSubjectIds)
                ->orderBy('exam_date', 'desc')
                ->paginate(20);

            $currentSchoolYear = SchoolYear::current();

            return view('evaluations.teacher-index', compact('evaluations', 'currentSchoolYear'));

        } catch (\Exception $e) {
            Log::error('Erreur dans teacherEvaluations: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.');
        }
    }

    // ========== MÉTHODES UTILITAIRES ==========

    /**
     * Vérifier les permissions des enseignants
     */
    private function checkTeacherPermissions(Evaluation $evaluation)
    {
        $user = Auth::user();

        if ($user->hasRole(['enseignant', 'enseignant titulaire'])) {
            $isAssigned = TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $evaluation->class_id)
                ->where('subject_id', $evaluation->subject_id)
                ->exists();

            if (!$isAssigned) {
                throw new \Illuminate\Auth\Access\AuthorizationException(
                    'Vous n\'êtes pas assigné à cette classe/matière.'
                );
            }
        }
    }

    /**
     * Calculer le pourcentage de complétion
     */
    private function getCompletionPercentage(Evaluation $evaluation)
    {
        $totalStudents = $evaluation->class->students()->count();

        if ($totalStudents === 0) {
            return 0;
        }

        $markedStudents = $evaluation->marks()->count();

        return round(($markedStudents / $totalStudents) * 100, 2);
    }

    /**
     * Enregistrer une activité
     */
    private function logActivity($action, $model, $modelId, $description, $oldValues = null, $newValues = null)
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
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]);
        } catch (\Exception $e) {
            Log::warning('Erreur lors de l\'enregistrement d\'une activité: ' . $e->getMessage());
        }
    }
}
