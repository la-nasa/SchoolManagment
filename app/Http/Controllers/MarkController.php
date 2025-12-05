<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Mark;
use App\Models\Evaluation;
use App\Models\Student;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Services\MarkCalculationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MarkController extends Controller
{
    protected $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->middleware('auth');
        $this->calculationService = $calculationService;
    }

    /**
     * Afficher le formulaire de création des notes
     */
    public function create(Evaluation $evaluation)
    {
        try {
            $this->authorize('create-marks');

            // 1. Vérifier que l'évaluation existe
            if (!$evaluation || !$evaluation->exists) {
                Log::warning('Évaluation invalide: ID manquant ou null');
                return redirect()->route('admin.evaluations.index')
                    ->with('error', 'L\'évaluation spécifiée n\'existe pas.');
            }

            // 2. Charger les relations
            $evaluation->load([
                'class' => function($query) {
                    $query->with(['students' => function($q) {
                        $q->orderBy('first_name')->orderBy('last_name');
                    }]);
                },
                'subject',
                'examType',
                'term',
                'schoolYear',
            ]);

            // 3. Vérifier la classe
            if (!$evaluation->class) {
                Log::error('Classe manquante pour évaluation ID: ' . $evaluation->id);
                return redirect()->route('admin.evaluations.show', $evaluation)
                    ->with('error', 'La classe associée à cette évaluation est introuvable.');
            }

            // 4. Vérifier les permissions
            $this->checkTeacherPermissions($evaluation);

            // 5. Récupérer les élèves
            $students = $evaluation->class->students;

            if ($students->isEmpty()) {
                Log::warning('Aucun élève dans la classe ' . $evaluation->class->id);
                return redirect()->route('admin.evaluations.show', $evaluation)
                    ->with('warning', 'Aucun élève trouvé dans cette classe.');
            }

            // 6. Charger les notes existantes
            $existingMarks = $evaluation->marks()
                ->with('student')
                ->get()
                ->keyBy('student_id');

            Log::info('Formulaire de saisie des notes chargé', [
                'evaluation_id' => $evaluation->id,
                'students_count' => $students->count(),
                'marks_count' => $existingMarks->count(),
                'user_id' => Auth::id(),
            ]);

            return view('marks.create', compact('evaluation', 'students', 'existingMarks'));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Accès non autorisé - MarkController::create', [
                'user_id' => Auth::id(),
                'evaluation_id' => $evaluation->id ?? null,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('admin.evaluations.index')
                ->with('error', 'Vous n\'êtes pas autorisé à accéder à cette évaluation.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Évaluation non trouvée - MarkController::create');
            return redirect()->route('admin.evaluations.index')
                ->with('error', 'L\'évaluation spécifiée n\'existe pas.');

        } catch (\Throwable $e) {
            Log::error('Erreur dans MarkController::create', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.evaluations.index')
                ->with('error', 'Une erreur est survenue lors du chargement du formulaire de saisie des notes.');
        }
    }

    /**
     * Enregistrer les notes
     */
    public function store(Request $request, Evaluation $evaluation)
    {
        $this->authorize('create-marks');

        try {
            // 1. Valider l'évaluation
            if (!$evaluation || !$evaluation->exists) {
                return redirect()->route('admin.evaluations.index')
                    ->with('error', 'L\'évaluation n\'existe pas.');
            }

            // 2. Charger les relations
            $evaluation->load(['class', 'subject', 'term', 'schoolYear']);

            // 3. Vérifier les permissions
            $this->checkTeacherPermissions($evaluation);

            // 4. Valider les données
            $validated = $request->validate([
                'marks' => 'required|array',
                'marks.*.student_id' => 'required|exists:students,id',
                'marks.*.marks' => 'nullable|numeric|min:0|max:' . ($evaluation->max_marks ?? 20),
                'marks.*.is_absent' => 'boolean',
                'marks.*.comment' => 'nullable|string|max:500',
            ]);

            // 5. Enregistrer les notes
            $result = $this->saveMarksData($validated, $evaluation);

            $this->logActivity(
                'create_marks',
                'Mark',
                $evaluation->id,
                "Saisie de {$result['processed']} note(s) pour {$evaluation->subject->name} - {$evaluation->class->name}"
            );

            $message = "✓ Notes enregistrées avec succès! ";
            $message .= "{$result['processed']} note(s) saisie(s), ";
            $message .= "{$result['absent']} absent(s).";

            return redirect()->route('admin.evaluations.show', $evaluation)
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation échouée - MarkController::store', $e->errors());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Veuillez vérifier les données saisies.');

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Accès non autorisé - MarkController::store');
            return redirect()->route('admin.evaluations.index')
                ->with('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');

        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'enregistrement des notes', [
                'message' => $e->getMessage(),
                'evaluation_id' => $evaluation->id ?? null,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de l\'enregistrement.')
                ->withInput();
        }
    }

    /**
     * Éditer une note
     */
    public function edit(Mark $mark)
    {
        $this->authorize('edit-marks');

        try {
            $mark->load(['evaluation', 'student.user', 'subject']);

            $this->checkTeacherPermissions($mark->evaluation);

            return view('marks.edit', compact('mark'));

        } catch (\Throwable $e) {
            Log::error('Erreur dans MarkController::edit', [
                'message' => $e->getMessage()
            ]);
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.');
        }
    }

    /**
     * Mettre à jour une note
     */
    public function update(Request $request, Mark $mark)
    {
        $this->authorize('edit-marks');

        try {
            $this->checkTeacherPermissions($mark->evaluation);

            $validated = $request->validate([
                'marks' => 'nullable|numeric|min:0|max:' . ($mark->evaluation->max_marks ?? 20),
                'is_absent' => 'boolean',
                'comment' => 'nullable|string|max:500',
            ]);

            $oldValues = $mark->only(['marks', 'is_absent', 'comment']);

            if ($request->boolean('is_absent')) {
                $validated['marks'] = 0;
                $validated['comment'] = $validated['comment'] ?? 'Absent';
            }

            $mark->update($validated);

            $this->calculationService->calculateAllSubjectAverages(
                $mark->evaluation->class,
                $mark->evaluation->term,
                $mark->evaluation->schoolYear
            );

            $this->logActivity('update_mark', 'Mark', $mark->id, 'Note mise à jour', $oldValues, $validated);

            return redirect()->route('admin.evaluations.show', $mark->evaluation)
                ->with('success', 'Note mise à jour avec succès.');

        } catch (\Throwable $e) {
            Log::error('Erreur dans MarkController::update', [
                'message' => $e->getMessage()
            ]);
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.')
                ->withInput();
        }
    }

    /**
     * Supprimer une note
     */
    public function destroy(Mark $mark)
    {
        $this->authorize('delete-marks');

        try {
            $this->checkTeacherPermissions($mark->evaluation);

            $evaluation = $mark->evaluation;
            $mark->delete();

            $this->calculationService->calculateAllSubjectAverages(
                $evaluation->class,
                $evaluation->term,
                $evaluation->schoolYear
            );

            $this->logActivity('delete_mark', 'Mark', null, 'Note supprimée');

            return redirect()->route('admin.evaluations.show', $evaluation)
                ->with('success', 'Note supprimée avec succès.');

        } catch (\Throwable $e) {
            Log::error('Erreur dans MarkController::destroy', [
                'message' => $e->getMessage()
            ]);
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.');
        }
    }

    // ========== MÉTHODES UTILITAIRES ==========

    /**
     * Vérifier les permissions
     */
    private function checkTeacherPermissions(Evaluation $evaluation)
    {
        $user = Auth::user();

        // Les administrateurs ont accès
        if ($user->hasRole('administrateur')) {
            return;
        }

        // Les enseignants doivent être assignés
        if ($user->hasRole(['enseignant', 'enseignant titulaire'])) {
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
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
     * Enregistrer les notes
     */
    private function saveMarksData(array $validated, Evaluation $evaluation)
    {
        $processedCount = 0;
        $absentCount = 0;

        DB::beginTransaction();

        try {
            foreach ($validated['marks'] as $studentData) {
                $studentId = $studentData['student_id'];
                $markValue = $studentData['marks'] ?? null;
                $isAbsent = $studentData['is_absent'] ?? false;
                $comment = $studentData['comment'] ?? null;

                if ($isAbsent) {
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
                }
            }

            $this->calculationService->calculateAllSubjectAverages(
                $evaluation->class,
                $evaluation->term,
                $evaluation->schoolYear
            );

            DB::commit();

            return [
                'processed' => $processedCount,
                'absent' => $absentCount
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
            Log::warning('Erreur lors du log activité: ' . $e->getMessage());
        }
    }
}