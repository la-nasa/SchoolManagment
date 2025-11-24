<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Evaluation;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\ExamType;
use App\Models\Term;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view-evaluations');

        $query = Evaluation::with(['class', 'subject', 'examType', 'term']);

        // Filtres selon le rôle
        $user = $request->user();

        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $assignedClassIds = $user->teacherAssignments()->pluck('class_id');
            $assignedSubjectIds = $user->teacherAssignments()->pluck('subject_id');

            $query->whereIn('class_id', $assignedClassIds)
                  ->whereIn('subject_id', $assignedSubjectIds);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        $evaluations = $query->orderBy('exam_date', 'desc')->paginate(20);

        $classes = Classe::active()->get();
        $subjects = Subject::active()->get();
        $terms = Term::all();
        $currentSchoolYear = SchoolYear::current();

        return view('evaluations.index', compact('evaluations', 'classes', 'subjects', 'terms', 'currentSchoolYear'));
    }

    public function create()
    {
        $this->authorize('create-evaluations');

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

        return view('evaluations.create', compact('classes', 'subjects', 'examTypes', 'terms','schoolYears', 'currentSchoolYear', 'teachers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-evaluations');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'term_id' => 'required|exists:terms,id',
            'school_year_id' => 'required|exists:school_years,id',
            'max_marks' => 'required|numeric|min:0|max:100',
            'pass_marks' => 'required|numeric|min:0|max:100|lte:max_marks',
            'description' => 'nullable|string',
        ]);

        // Vérifier les permissions pour les enseignants
        $user = $request->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $validated['class_id'])
                ->where('subject_id', $validated['subject_id'])
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette classe/matière.');
            }
            $validated['teacher_id'] = $user->id;

        } else {
            // Pour les administrateurs, utiliser l'enseignant assigné à la classe/matière
            $assignment = \App\Models\TeacherAssignment::where('class_id', $validated['class_id'])
                ->where('subject_id', $validated['subject_id'])
                ->first();

            if ($assignment) {
                $validated['teacher_id'] = $assignment->teacher_id;
            }
        }


        Evaluation::create($validated);

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Évaluation créée avec succès.');
    }

    public function show(Evaluation $evaluation)
    {
        $this->authorize('view-evaluations');

        // Vérifier les permissions pour les enseignants
        $user = request()->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $evaluation->class_id)
                ->where('subject_id', $evaluation->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette classe/matière.');
            }
        }

        $evaluation->load(['class.teacher', 'subject', 'examType', 'term', 'schoolYear', 'marks.student']);

        $completionPercentage = $evaluation->completion_percentage;
        $missingMarks = $evaluation->class->students()->whereNotIn('id', $evaluation->marks->pluck('student_id'))->get();

        return view('evaluations.show', compact('evaluation', 'completionPercentage', 'missingMarks'));
    }

    public function edit(Evaluation $evaluation)
    {
        $this->authorize('edit-evaluations');

        // Vérifier les permissions pour les enseignants
        $user = request()->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $evaluation->class_id)
                ->where('subject_id', $evaluation->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette classe/matière.');
            }
        }

        $classes = Classe::active()->get();
        $subjects = Subject::active()->get();
        $examTypes = ExamType::all();
        $terms = Term::all();
        $schoolYears = SchoolYear::all();
        $teachers = User::role(['enseignant', 'enseignant titulaire'])
            ->where('is_active', true)
            ->get();

        return view('evaluations.edit', compact('evaluation', 'classes', 'subjects', 'examTypes', 'terms', 'schoolYears', 'teachers'));
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        $this->authorize('edit-evaluations');

        // Vérifier les permissions pour les enseignants
        $user = $request->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $evaluation->class_id)
                ->where('subject_id', $evaluation->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette classe/matière.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'term_id' => 'required|exists:terms,id',
            'school_year_id' => 'required|exists:school_years,id',
            'max_marks' => 'required|numeric|min:0|max:100',
            'pass_marks' => 'required|numeric|min:0|max:100|lte:max_marks',
            'description' => 'nullable|string',
        ]);

        $evaluation->update($validated);

        return redirect()->route('admin.evaluations.show', $evaluation)
            ->with('success', 'Évaluation mise à jour avec succès.');
    }

    public function destroy(Evaluation $evaluation)
    {
        $this->authorize('delete-evaluations');

        // Vérifier les permissions pour les enseignants
        $user = request()->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $evaluation->class_id)
                ->where('subject_id', $evaluation->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette classe/matière.');
            }
        }

        // Supprimer les notes associées
        $evaluation->marks()->delete();
        $evaluation->delete();

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Évaluation supprimée avec succès.');
    }

    public function showMarks(Evaluation $evaluation)
    {
        $this->authorize('view-evaluations');

        // Vérifier les permissions pour les enseignants
        $user = request()->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $evaluation->class_id)
                ->where('subject_id', $evaluation->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette classe/matière.');
            }
        }

        $evaluation->load(['class.students.user', 'marks.student']);

        $students = $evaluation->class->students()->with(['marks' => function($query) use ($evaluation) {
            $query->where('evaluation_id', $evaluation->id);
        }])->get();

        return view('evaluations.marks', compact('evaluation', 'students'));
    }

    // Méthodes spécifiques pour les enseignants
    public function teacherEvaluations(Request $request)
    {
        $user = $request->user();

        $assignedClassIds = $user->teacherAssignments()->pluck('class_id');
        $assignedSubjectIds = $user->teacherAssignments()->pluck('subject_id');

        $evaluations = Evaluation::with(['class', 'subject', 'examType', 'term'])
            ->whereIn('class_id', $assignedClassIds)
            ->whereIn('subject_id', $assignedSubjectIds)
            ->orderBy('exam_date', 'desc')
            ->paginate(20);

        $currentSchoolYear = SchoolYear::current();

        return view('evaluations.teacher-index', compact('evaluations', 'currentSchoolYear'));
    }

    public function teacherShow(Evaluation $evaluation)
    {
        $user = request()->user();

        // Vérifier que l'enseignant est assigné à cette évaluation
        $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
            ->where('class_id', $evaluation->class_id)
            ->where('subject_id', $evaluation->subject_id)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'Vous n\'êtes pas assigné à cette évaluation.');
        }

        $evaluation->load(['class', 'subject', 'examType', 'term', 'schoolYear', 'marks.student']);

        $completionPercentage = $evaluation->completion_percentage;
        $missingMarks = $evaluation->class->students()->whereNotIn('id', $evaluation->marks->pluck('student_id'))->get();

        return view('evaluations.teacher-show', compact('evaluation', 'completionPercentage', 'missingMarks'));
    }
}
