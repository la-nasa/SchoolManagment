<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Mark;
use App\Models\Evaluation;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Services\MarkCalculationService;

class MarkController extends Controller
{
    protected $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->middleware('auth');
        $this->calculationService = $calculationService;
    }

    public function create(Request $request)
{
    $this->authorize('create-marks');

    try {
        // Récupérer l'évaluation
        $evaluationId = $request->input('evaluation_id');

        if (!$evaluationId) {
            return redirect()->back()->with('error', 'ID d\'évaluation manquant.');
        }

        // Charger l'évaluation avec toutes les relations nécessaires
        $evaluation = Evaluation::with([
            'class.students',
            'subject',
            'examType',
            'term',
            'schoolYear',
            'marks.student'
        ])->findOrFail($evaluationId);

        // Vérifications de sécurité
        if (!$evaluation->class) {
            return redirect()->route('admin.evaluations.index')
                ->with('error', 'Cette évaluation n\'est associée à aucune classe.');
        }

        if (!$evaluation->class->students || $evaluation->class->students->isEmpty()) {
            return redirect()->route('admin.evaluations.show', $evaluation)
                ->with('error', 'Aucun étudiant trouvé dans cette classe.');
        }

        // Vérifier les permissions
        $user = $request->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $evaluation->class_id)
                ->where('subject_id', $evaluation->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette évaluation.');
            }
        }

        $students = $evaluation->class->students;
        $existingMarks = $evaluation->marks->keyBy('student_id');

        return view('marks.create', compact('evaluation', 'students', 'existingMarks'));

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return redirect()->route('admin.evaluations.index')
            ->with('error', 'Évaluation non trouvée.');
    } catch (\Exception $e) {
        return redirect()->route('admin.evaluations.index')
            ->with('error', 'Erreur lors du chargement de l\'évaluation: ' . $e->getMessage());
    }
}

    public function store(Request $request)
{
    $this->authorize('create-marks');

    // Debug simple
    // dd($request->all()); // Décommentez pour voir toutes les données

    $evaluationId = $request->input('evaluation_id');

    if (!$evaluationId) {
        return redirect()->back()->with('error', 'Évaluation non spécifiée.')->withInput();
    }

    $evaluation = Evaluation::with(['class', 'subject', 'term', 'schoolYear'])->find($evaluationId);

    if (!$evaluation) {
        return redirect()->back()->with('error', 'Évaluation non trouvée.')->withInput();
    }

    // Valider les données
    $validated = $request->validate([
        'marks' => 'required|array',
        'marks.*.student_id' => 'required|exists:students,id',
        'marks.*.mark' => 'nullable|numeric|min:0|max:' . ($evaluation->max_marks ?? 20),
        'marks.*.is_absent' => 'boolean',
        'marks.*.comment' => 'nullable|string|max:500',
    ]);

    try {
        DB::beginTransaction();

        $processedCount = 0;
        $absentCount = 0;

        foreach ($validated['marks'] as $studentData) {
            $studentId = $studentData['student_id'];
            $markValue = $studentData['mark'] ?? null;
            $isAbsent = $studentData['is_absent'] ?? false;
            $comment = $studentData['comment'] ?? null;

            if ($isAbsent) {
                // Marquer comme absent
                Mark::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'evaluation_id' => $evaluation->id,
                    ],
                    [
                        'subject_id' => $evaluation->subject_id,
                        'class_id' => $evaluation->class_id,
                        'term_id' => $evaluation->term_id,
                        'school_year_id' => $evaluation->school_year_id,
                        'marks' => 0,
                        'is_absent' => true,
                        'comment' => $comment ?: 'Absent',
                    ]
                );
                $absentCount++;

            } elseif ($markValue !== null && $markValue !== '') {
                // Enregistrer la note
                Mark::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'evaluation_id' => $evaluation->id,
                    ],
                    [
                        'subject_id' => $evaluation->subject_id,
                        'class_id' => $evaluation->class_id,
                        'term_id' => $evaluation->term_id,
                        'school_year_id' => $evaluation->school_year_id,
                        'marks' => $markValue,
                        'is_absent' => false,
                        'comment' => $comment,
                    ]
                );
                $processedCount++;

            } else {
                // Aucune donnée - supprimer si existe
                Mark::where('student_id', $studentId)
                    ->where('evaluation_id', $evaluation->id)
                    ->delete();
            }
        }

        DB::commit();

        $message = "Notes enregistrées avec succès! ";
        $message .= "{$processedCount} note(s) saisie(s), ";
        $message .= "{$absentCount} absent(s) marqué(s).";

        return redirect()->route('admin.evaluations.show', $evaluation)
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();

        // Pour debug, vous pouvez utiliser dd() ici aussi
        // dd($e->getMessage());

        return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage())->withInput();
    }
}
    public function edit(Mark $mark)
    {
        $this->authorize('edit-marks');

        // Vérifier les permissions pour les enseignants
        $user = request()->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $mark->class_id)
                ->where('subject_id', $mark->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette note.');
            }
        }

        $mark->load(['evaluation', 'student', 'subject']);

        return view('marks.edit', compact('mark'));
    }

    public function update(Request $request, Mark $mark)
    {
        $this->authorize('edit-marks');

        // Vérifier les permissions pour les enseignants
        $user = $request->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $mark->class_id)
                ->where('subject_id', $mark->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette note.');
            }
        }

        $validated = $request->validate([
            'marks' => 'required_if:is_absent,0|nullable|numeric|min:0|max:' . $mark->evaluation->max_marks,
            'is_absent' => 'boolean',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validated['is_absent'] ?? false) {
            $validated['marks'] = 0;
            $validated['comment'] = $validated['comment'] ?? 'Absent';
        }

        $mark->update($validated);

        // Recalculer les moyennes
        $this->calculationService->calculateAllSubjectAverages(
            $mark->class,
            $mark->term,
            $mark->schoolYear
        );

        return redirect()->route('evaluations.show', $mark->evaluation)
            ->with('success', 'Note mise à jour avec succès.');
    }

    public function destroy(Mark $mark)
    {
        $this->authorize('delete-marks');

        // Vérifier les permissions pour les enseignants
        $user = request()->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $mark->class_id)
                ->where('subject_id', $mark->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette note.');
            }
        }

        $evaluation = $mark->evaluation;
        $mark->delete();

        // Recalculer les moyennes
        $this->calculationService->calculateAllSubjectAverages(
            $evaluation->class,
            $evaluation->term,
            $evaluation->schoolYear
        );

        return redirect()->route('evaluations.show', $evaluation)
            ->with('success', 'Note supprimée avec succès.');
    }

    // Méthodes spécifiques pour les enseignants
    public function teacherCreate(Evaluation $evaluation)
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

        $evaluation->load(['class.students', 'subject']);
        $students = $evaluation->class->students;
        $existingMarks = $evaluation->marks->keyBy('student_id');

        return view('marks.teacher-create', compact('evaluation', 'students', 'existingMarks'));
    }

    public function teacherStore(Request $request, Evaluation $evaluation)
    {
        return $this->store($request, $evaluation);
    }

    public function teacherEdit(Mark $mark)
    {
        $user = request()->user();

        // Vérifier que l'enseignant est assigné à cette note
        $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
            ->where('class_id', $mark->class_id)
            ->where('subject_id', $mark->subject_id)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'Vous n\'êtes pas assigné à cette note.');
        }

        $mark->load(['evaluation', 'student', 'subject']);

        return view('marks.teacher-edit', compact('mark'));
    }

    public function teacherUpdate(Request $request, Mark $mark)
    {
        return $this->update($request, $mark);
    }

    public function bulkUpdate(Request $request, Evaluation $evaluation)
    {
        $this->authorize('edit-marks');

        // Vérifier les permissions pour les enseignants
        $user = $request->user();
        if ($user->isTeacher() || $user->isTitularTeacher()) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $evaluation->class_id)
                ->where('subject_id', $evaluation->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette évaluation.');
            }
        }

        $validated = $request->validate([
            'marks' => 'required|array',
            'marks.*.id' => 'required|exists:marks,id',
            'marks.*.marks' => 'nullable|numeric|min:0|max:' . $evaluation->max_marks,
            'marks.*.is_absent' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $evaluation) {
            foreach ($validated['marks'] as $markData) {
                $mark = Mark::find($markData['id']);

                if ($mark && $mark->evaluation_id === $evaluation->id) {
                    if ($markData['is_absent'] ?? false) {
                        $mark->update([
                            'marks' => 0,
                            'is_absent' => true,
                            'comment' => 'Absent',
                        ]);
                    } else {
                        $mark->update([
                            'marks' => $markData['marks'],
                            'is_absent' => false,
                            'comment' => null,
                        ]);
                    }
                }
            }

            // Recalculer les moyennes
            $this->calculationService->calculateAllSubjectAverages(
                $evaluation->class,
                $evaluation->term,
                $evaluation->schoolYear
            );
        });

        return response()->json(['success' => true, 'message' => 'Notes mises à jour avec succès.']);
    }
}
