<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Term;
use App\Models\Average;
use App\Models\SchoolYear;
use App\Models\Bulletin;
use App\Models\Evaluation;
use App\Models\Student;
use App\Models\SchoolSetting;
use App\Services\MarkCalculationService;
use App\Models\Subject;
use App\Models\Mark;
use App\Models\GeneralAverage;
use App\Models\Teacher;
use App\Models\ExamType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BulletinController extends Controller
{
    protected $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->middleware('auth');
        $this->calculationService = $calculationService;
    }

    /**
     * Générer et sauvegarder les bulletins pour une classe (en base).
     */
    public function generateForClass(Request $request, Classe $classe)
    {
        $this->authorize('generate-bulletins');

        $term = Term::findOrFail($request->input('term_id'));
        $schoolYear = SchoolYear::findOrFail($request->input('school_year_id'));
        $type = $request->input('type', 'standard'); // standard|apc

        try {
            // recalculer moyennes avant génération
            $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
            $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

            $students = $classe->students()->with('user')->get();

            DB::transaction(function () use ($students, $classe, $term, $schoolYear, $type) {
                foreach ($students as $student) {
                    $general = GeneralAverage::where([
                        'student_id' => $student->id,
                        'classe_id' => $classe->id,
                        'term_id' => $term->id,
                        'school_year_id' => $schoolYear->id,
                    ])->first();

                    $averages = $student->averages()->where([
                        'class_id' => $classe->id,
                        'term_id' => $term->id,
                        'school_year_id' => $schoolYear->id,
                    ])->with('subject')->get()->map(function($a){
                        return [
                            'subject_id' => $a->subject_id,
                            'subject' => $a->subject->name ?? null,
                            'average' => $a->average,
                        ];
                    })->toArray();

                    $content = [
                        'student' => [
                            'id' => $student->id,
                            'name' => ($student->user->first_name ?? '') . ' ' . ($student->user->last_name ?? ''),
                            'matricule' => $student->matricule ?? null,
                        ],
                        'averages' => $averages,
                        'general' => $general ? $general->average : null,
                        'rank' => $general ? $general->rank : null,
                        'appreciation' => $general ? $general->appreciation : null,
                    ];

                    Bulletin::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'class_id' => $classe->id,
                            'term_id' => $term->id,
                            'school_year_id' => $schoolYear->id,
                            'type' => $type,
                        ],
                        [
                            'content' => $content,
                            'generated_by' => Auth::id(),
                            'generated_at' => now(),
                        ]
                    );
                }
            });

            return redirect()->back()->with('success', 'Bulletins générés et enregistrés.');

        } catch (\Exception $e) {
            Log::error('Erreur génération bulletins: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Erreur lors de la génération des bulletins.');
        }
    }

    /**
     * Générer le bulletin PDF d'un étudiant et le retourner (download)
     */
    public function generateStudentBulletin(Request $request, $studentId)
    {
        $this->authorize('generate-reports');

        set_time_limit(120);

        try {
            Log::info("=== DÉBUT GÉNÉRATION BULLETIN ÉTUDIANT ===");

            $student = Student::find($studentId);
            if (!$student) {
                return back()->with('error', 'Élève non trouvé.');
            }

            if (!$student->class_id) {
                return back()->with('error', "Cet élève n'est pas assigné à une classe.");
            }

            $request->validate([
                'term_id' => 'required|exists:terms,id',
                'school_year_id' => 'required|exists:school_years,id',
                'type' => 'nullable|in:standard,apc'
            ]);

            $termId = $request->input('term_id');
            $schoolYearId = $request->input('school_year_id');
            $bulletinType = $request->input('type', 'standard');

            $term = Term::findOrFail($termId);
            $schoolYear = SchoolYear::findOrFail($schoolYearId);

            $classe = Classe::find($student->class_id);
            if (!$classe) {
                return back()->with('error', "La classe de l'élève n'existe pas.");
            }

            $schoolSettings = SchoolSetting::first();

            Log::info("Étudiant: {$student->full_name}, Classe: {$classe->name}");

            // Calculs
            $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
            $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

            $results = $this->calculationService->calculateStudentResults(
                $student->id,
                $schoolYear->id,
                $term->id
            );

            if (empty($results['subject_results'])) {
                Log::warning("Aucune matière trouvée pour l'étudiant {$student->id}");
                return back()->with('error', 'Aucune donnée académique disponible.');
            }

            // Statistiques de classe (fallback si nécessaire)
            $classStats = $this->calculationService->calculateClassStatistics(
                $classe->id,
                $term->id,
                $schoolYear->id
            );

            if (empty($classStats) || !is_array($classStats)) {
                $classStats = $this->getClassStatisticsFallback($classe, $term, $schoolYear);
            }

            // Préparer données
            $data = [
                'student' => $student,
                'schoolYear' => $schoolYear,
                'term' => $term,
                'results' => $results,
                'settings' => $schoolSettings,
                'classStats' => $classStats,
                'classe' => $classe,
                'type' => $bulletinType,
                'examTypes' => ExamType::all()
            ];

            $viewName = $bulletinType === 'apc' ? 'reports.bulletin-apc' : 'reports.bulletin-standard';

            Log::info("Génération PDF avec vue: {$viewName}");

            // Nettoyer buffers pour éviter corruption
            while (ob_get_level()) {
                ob_end_clean();
            }

            $pdf = Pdf::loadView($viewName, $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'enable_php' => false,
                    'dpi' => 96,
                    'enable_font_subsetting' => true,
                    'compress' => true,
                ]);

            $filename = "bulletin_{$student->matricule}_{$term->name}_{$schoolYear->year}.pdf";

            // Stream en téléchargement (facade s'occupe des headers)
            Log::info("=== BULLETIN GÉNÉRÉ AVEC SUCCÈS ===");
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('ERREUR GÉNÉRATION BULLETIN ÉTUDIANT: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Variante simple (avec tests de rendu HTML avant PDF)
     */
    public function generateStudentBulletinSimple(Request $request, $studentId)
    {
        $this->authorize('generate-reports');
        set_time_limit(300);

        try {
            Log::info("=== DÉBUT GÉNÉRATION BULLETIN SIMPLE ===");

            $student = Student::with(['classe', 'classe.teacher'])->find($studentId);
            if (!$student) {
                return back()->with('error', 'Élève non trouvé.');
            }

            if (!$student->class_id) {
                return back()->with('error', "Cet élève n'est pas assigné à une classe.");
            }

            $request->validate([
                'term_id' => 'required|exists:terms,id',
                'school_year_id' => 'required|exists:school_years,id',
                'type' => 'nullable|in:standard,apc'
            ]);

            $termId = $request->input('term_id');
            $schoolYearId = $request->input('school_year_id');
            $bulletinType = $request->input('type', 'standard');

            $term = Term::findOrFail($termId);
            $schoolYear = SchoolYear::findOrFail($schoolYearId);
            $schoolSettings = SchoolSetting::first();

            $classe = Classe::with('teacher')->find($student->class_id);
            if (!$classe) {
                return back()->with('error', "La classe de l'élève n'existe pas.");
            }

            Log::info("Étudiant: {$student->full_name}, Classe: {$classe->name}");

            $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
            $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

            $results = $this->calculationService->calculateStudentResults(
                $student->id,
                $schoolYear->id,
                $term->id
            );

            Log::info("Résultats obtenus", [
                'moyenne' => $results['general_average'] ?? 0,
                'matieres' => count($results['subject_results'] ?? []),
                'rang' => $results['rank'] ?? 0
            ]);

            if (empty($results['subject_results'])) {
                Log::warning("Aucune matière trouvée pour l'étudiant {$student->id}");
                return back()->with('error', 'Aucune donnée académique disponible. Vérifiez les notes.');
            }

            $classStats = $this->calculationService->calculateClassStatistics(
                $classe,
                $term,
                $schoolYear
            );

            if (empty($classStats) || !is_array($classStats)) {
                Log::warning('calculateClassStatistics a retourné des données invalides, utilisation de valeurs par défaut');
                $classStats = $this->getClassStatisticsFallback($classe, $term, $schoolYear);
            }

            $bulletin = Bulletin::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'school_year_id' => $schoolYear->id,
                    'term_id' => $term->id
                ],
                [
                    'class_id' => $student->class_id,
                    'average' => $results['general_average'] ?? 0,
                    'rank' => $results['rank'] ?? 0,
                    'appreciation' => $results['appreciation'] ?? 'Non noté',
                    'generated_by' => auth()->id(),
                    'generated_at' => now(),
                    'type' => $bulletinType
                ]
            );

            $data = [
                'student' => $student,
                'schoolYear' => $schoolYear,
                'term' => $term,
                'results' => $results,
                'settings' => $schoolSettings,
                'classStats' => $classStats,
                'classe' => $classe,
                'type' => $bulletinType,
                'examTypes' => ExamType::all(),
                'bulletin' => $bulletin,
                'competences' => $this->getCompetencesBySubject()
            ];

            $viewName = $bulletinType === 'apc' ? 'reports.bulletin-apc' : 'reports.bulletin-standard';

            // Test render
            try {
                $html = view($viewName, $data)->render();
                Log::info("HTML généré avec succès: " . strlen($html) . " caractères");
            } catch (\Exception $e) {
                Log::error("Erreur rendu vue: " . $e->getMessage());
                $viewName = 'reports.simple-bulletin';
            }

            while (ob_get_level()) {
                ob_end_clean();
            }

            $pdf = Pdf::loadView($viewName, $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'enable_php' => false,
                    'dpi' => 96,
                    'enable_font_subsetting' => true,
                    'compress' => true,
                ]);

            $filename = "bulletin_{$student->matricule}_{$term->name}_{$schoolYear->year}.pdf";

            Log::info("=== BULLETIN GÉNÉRÉ AVEC SUCCÈS ===");
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('ERREUR GÉNÉRATION BULLETIN SIMPLE: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Générer les bulletins pour toute une classe et renvoyer un ZIP.
     */
    public function generateClassBulletins(Classe $classe, Request $request)
    {
        $this->authorize('generate-reports');

        $tempDir = null;
        $zipPath = null;

        try {
            $request->validate([
                'term_id' => 'required|exists:terms,id',
                'school_year_id' => 'required|exists:school_years,id',
                'type' => 'nullable|in:standard,apc'
            ]);

            $termId = $request->input('term_id');
            $schoolYearId = $request->input('school_year_id');
            $bulletinType = $request->input('type', 'standard');

            $term = Term::findOrFail($termId);
            $schoolYear = SchoolYear::findOrFail($schoolYearId);
            $schoolSettings = SchoolSetting::first();

            $students = $classe->students()
                ->whereNotNull('class_id')
                ->where('is_active', true)
                ->get();

            if ($students->isEmpty()) {
                return back()->with('error', 'Aucun étudiant valide dans cette classe.');
            }

            Log::info('Début génération bulletins classe', [
                'classe' => $classe->id,
                'term' => $term->id,
                'school_year' => $schoolYear->id,
                'nombre_etudiants' => $students->count()
            ]);

            $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
            $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

            $tempDir = storage_path('app/temp/bulletins_' . Str::random(20));
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            $zipFilename = "bulletins_{$classe->name}_{$term->name}_{$schoolYear->year}.zip";
            $zipPath = $tempDir . '/' . $zipFilename;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Impossible de créer le fichier ZIP");
            }

            $generatedCount = 0;
            $errors = [];

            foreach ($students as $student) {
                try {
                    set_time_limit(30);

                    $results = $this->calculationService->calculateStudentResults(
                        $student->id,
                        $schoolYear->id,
                        $term->id
                    );

                    if (empty($results['subject_results'])) {
                        $errors[] = "{$student->matricule}: Aucune note trouvée";
                        continue;
                    }

                    $classStats = $this->calculationService->calculateClassStatistics(
                        $classe->id,
                        $term->id,
                        $schoolYear->id
                    );

                    // Enregistrer le bulletin résumé
                    Bulletin::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'school_year_id' => $schoolYear->id,
                            'term_id' => $term->id
                        ],
                        [
                            'class_id' => $classe->id,
                            'average' => $results['general_average'] ?? 0,
                            'rank' => $results['rank'] ?? 0,
                            'appreciation' => $results['appreciation'] ?? 'Non noté',
                            'generated_by' => Auth::id(),
                            'generated_at' => now(),
                            'type' => $bulletinType
                        ]
                    );

                    $data = [
                        'student' => $student,
                        'schoolYear' => $schoolYear,
                        'term' => $term,
                        'results' => $results,
                        'settings' => $schoolSettings,
                        'classStats' => $classStats,
                        'classe' => $classe,
                        'type' => $bulletinType,
                        'examTypes' => ExamType::all()
                    ];

                    $viewName = $bulletinType === 'apc' ? 'reports.bulletin-apc' : 'reports.bulletin-standard';

                    // Génération PDF : output binaire -> sauvegarde temporaire -> ajout ZIP
                    while (ob_get_level()) {
                        ob_end_clean();
                    }

                    $pdf = Pdf::loadView($viewName, $data)
                        ->setPaper('a4', 'portrait')
                        ->setOptions([
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => true,
                            'defaultFont' => 'DejaVu Sans',
                            'enable_php' => false,
                            'dpi' => 96,
                            'enable_font_subsetting' => true,
                            'compress' => true,
                        ]);

                    $tempPdfFilename = "bulletin_{$student->matricule}_{$term->name}.pdf";
                    $tempPdfPath = $tempDir . '/' . $tempPdfFilename;

                    // Sauvegarder en binaire
                    $pdfContent = $pdf->output();
                    file_put_contents($tempPdfPath, $pdfContent, LOCK_EX);

                    clearstatcache(true, $tempPdfPath);
                    if (!file_exists($tempPdfPath) || filesize($tempPdfPath) < 200) {
                        throw new \Exception("Fichier PDF invalide pour {$student->matricule}");
                    }

                    if (!$zip->addFile($tempPdfPath, $tempPdfFilename)) {
                        throw new \Exception("Impossible d'ajouter {$tempPdfFilename} au ZIP");
                    }

                    $generatedCount++;
                    Log::debug("Bulletin généré pour {$student->full_name}");

                } catch (\Exception $e) {
                    $studentName = $student ? $student->full_name : "Étudiant inconnu";
                    $errors[] = "{$studentName}: " . $e->getMessage();
                    Log::error("Erreur génération bulletin étudiant: " . $e->getMessage(), ['student_id' => $student->id ?? null]);
                    continue;
                }
            }

            $zip->close();

            Log::info("Génération terminée", [
                'bulletins_generes' => $generatedCount,
                'total_etudiants' => $students->count(),
                'erreurs' => count($errors)
            ]);

            if ($generatedCount == 0) {
                File::deleteDirectory($tempDir);
                return back()->with('error', 'Aucun bulletin généré. ' . implode('; ', array_slice($errors, 0, 5)));
            }

            clearstatcache(true, $zipPath);
            if (!file_exists($zipPath) || filesize($zipPath) == 0) {
                File::deleteDirectory($tempDir);
                return back()->with('error', 'Le fichier ZIP généré est vide ou corrompu.');
            }

            // Retourner le ZIP et le supprimer après envoi
            return response()
                ->download($zipPath, $zipFilename)
                ->deleteFileAfterSend(true)
                ->setPrivate()
                ->setMaxAge(0);

        } catch (\Exception $e) {
            Log::error('Erreur génération bulletins classe: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($tempDir && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * PV (procès-verbal) par classe (version regroupée)
     */
    public function generateClassPV(Classe $classe, Request $request)
    {
        $this->authorize('generate-reports');

        try {
            $termId = $request->input('term_id');
            $schoolYearId = $request->input('school_year_id');

            if (!$termId || !$schoolYearId) {
                return back()->with('error', 'Veuillez sélectionner un trimestre et une année scolaire.');
            }

            $term = Term::findOrFail($termId);
            $schoolYear = SchoolYear::findOrFail($schoolYearId);
            $schoolSettings = SchoolSetting::first();

            $evaluations = Evaluation::where('class_id', $classe->id)
                ->where('term_id', $termId)
                ->where('school_year_id', $schoolYearId)
                ->with([
                    'subject',
                    'examType',
                    'marks.student'
                ])
                ->get();

            if ($evaluations->isEmpty()) {
                return back()->with('error', 'Aucune évaluation trouvée pour cette période');
            }

            $tempDir = storage_path('app/temp/pv_' . Str::random(10));
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            $zipFilename = 'pv.zip';
            $zipPath = $tempDir . '/' . $zipFilename;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', "Impossible de créer le fichier ZIP");
            }

            $generatedCount = 0;
            $errors = [];

            $allStudents = $classe->students()->get()->keyBy('id');

            foreach ($evaluations as $evaluation) {
                try {
                    $studentsWithMarks = [];

                    foreach ($allStudents as $student) {
                        $mark = $evaluation->marks->firstWhere('student_id', $student->id);

                        $studentsWithMarks[] = [
                            'student' => $student,
                            'marks' => $mark ? $mark->marks : 0,
                            'is_absent' => $mark ? $mark->is_absent : false,
                            'appreciation' => $mark ? $this->getMarkAppreciation($mark->marks) : '-',
                            'mark' => $mark
                        ];
                    }

                    usort($studentsWithMarks, function($a, $b) {
                        $nameA = ($a['student']->last_name ?? '') . ' ' . ($a['student']->first_name ?? '');
                        $nameB = ($b['student']->last_name ?? '') . ' ' . ($b['student']->first_name ?? '');
                        return strcmp($nameA, $nameB);
                    });

                    $data = [
                        'evaluation' => $evaluation,
                        'schoolYear' => $schoolYear,
                        'settings' => $schoolSettings,
                        'studentsWithMarks' => $studentsWithMarks,
                        'generatedBy' => Auth::user(),
                        'classe' => $classe,
                    ];

                    while (ob_get_level()) {
                        ob_end_clean();
                    }

                    $pdf = Pdf::loadView('reports.pv', $data)
                              ->setPaper('a4', 'landscape')
                              ->setOptions([
                                  'isHtml5ParserEnabled' => true,
                                  'isRemoteEnabled' => true,
                              ]);

                    $pdfContent = $pdf->output();
                    $filename = "PV_" . Str::slug($evaluation->subject->name ?? 'sujet') .
                               "_" . ($evaluation->exam_date ? $evaluation->exam_date->format('Y-m-d') : date('Y-m-d')) . ".pdf";

                    if ($zip->addFromString($filename, $pdfContent)) {
                        $generatedCount++;
                    } else {
                        throw new \Exception("Impossible d'ajouter {$filename} au ZIP");
                    }

                } catch (\Exception $e) {
                    $errors[] = "Évaluation {$evaluation->subject->name}: " . $e->getMessage();
                    Log::error("Erreur génération PV pour évaluation: " . $e->getMessage(), ['evaluation_id' => $evaluation->id]);
                    continue;
                }
            }

            $zip->close();

            if ($generatedCount == 0) {
                File::deleteDirectory($tempDir);
                return back()->with('error', 'Aucun PV généré. ' . ($errors[0] ?? ''));
            }

            $downloadFilename = "PV_{$classe->name}_{$term->name}_{$schoolYear->year}.zip";

            $response = response()->download($zipPath, $downloadFilename);
            $response->deleteFileAfterSend(true);

            register_shutdown_function(function() use ($tempDir) {
                if (File::exists($tempDir)) {
                    File::deleteDirectory($tempDir);
                }
            });

            return $response;

        } catch (\Exception $e) {
            Log::error('Erreur génération PV classe: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if (isset($tempDir) && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Helpers et utilitaires
     */
    private function validateBulletinData($student, $term, $schoolYear)
    {
        $errors = [];

        $marksCount = Mark::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where('school_year_id', $schoolYear->id)
            ->count();

        if ($marksCount === 0) {
            $errors[] = "Aucune note trouvée pour cet élève pour cette période.";
        }

        $averagesCount = Average::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where('school_year_id', $schoolYear->id)
            ->count();

        $evaluationsCount = Evaluation::where('class_id', $student->class_id)
            ->where('term_id', $term->id)
            ->where('school_year_id', $schoolYear->id)
            ->count();

        if ($evaluationsCount === 0) {
            $errors[] = "Aucune évaluation créée pour cette classe pour cette période.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => [
                'marks_count' => $marksCount,
                'averages_count' => $averagesCount,
                'evaluations_count' => $evaluationsCount
            ]
        ];
    }

    private function isClassTeacher($user, $classId)
    {
        if (!$user->hasRole('teacher')) {
            return false;
        }

        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            return false;
        }

        return \App\Models\TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->where('is_class_teacher', true)
            ->exists();
    }

    private function getMarkAppreciation($mark)
    {
        if ($mark >= 18) return 'Excellent';
        if ($mark >= 16) return 'Très bien';
        if ($mark >= 14) return 'Bien';
        if ($mark >= 12) return 'Assez bien';
        if ($mark >= 10) return 'Passable';
        return 'Insuffisant';
    }

    private function getAppreciation($average)
    {
        if ($average >= 18) return 'Excellent';
        if ($average >= 16) return 'Très Bien';
        if ($average >= 14) return 'Bien';
        if ($average >= 12) return 'Assez Bien';
        if ($average >= 10) return 'Passable';
        return 'Insuffisant';
    }

    private function getCompetencesBySubject()
    {
        return [
            'Français' => [
                'Maîtriser la langue française écrite et orale',
                'Analyser et interpréter des textes littéraires'
            ],
            'Mathématiques' => [
                'Résoudre des problèmes mathématiques complexes',
                'Appliquer des concepts algébriques et géométriques'
            ],
            'Sciences' => [
                'Appliquer la démarche scientifique',
                'Comprendre les principes fondamentaux'
            ]
        ];
    }

    private function getClassStatisticsFallback($classe, $term, $schoolYear)
    {
        return [
            'class_average' => 0,
            'max_average' => 0,
            'min_average' => 0,
            'success_rate' => 0,
            'total_students' => $classe->students()->count(),
            'top_average' => 0,
            'bottom_average' => 0,
            'class_id' => $classe->id,
            'class_name' => $classe->name,
            'term_name' => $term->name ?? 'Trimestre',
            'school_year' => $schoolYear->year ?? 'Année scolaire'
        ];
    }

    /**
     * Statistiques auxiliaires pour PV
     */
    private function calculateClassStatisticsForPV($studentsData)
    {
        if (empty($studentsData)) {
            return [
                'class_average' => 0,
                'min_average' => 0,
                'max_average' => 0,
                'success_rate' => 0,
                'standard_deviation' => 0,
                'median' => 0,
                'top_average' => 0,
                'bottom_average' => 0,
            ];
        }

        $averages = array_column($studentsData, 'general_average');
        $successCount = count(array_filter($averages, fn($avg) => $avg >= 10));

        return [
            'class_average' => array_sum($averages) / count($averages),
            'min_average' => min($averages),
            'max_average' => max($averages),
            'success_rate' => (count($averages) > 0) ? ($successCount / count($averages)) * 100 : 0,
            'standard_deviation' => $this->calculateStandardDeviation($averages),
            'median' => $this->calculateMedian($averages),
            'top_average' => max($averages),
            'bottom_average' => min($averages),
        ];
    }

    private function calculateSubjectStatistics($subjects, $studentsData)
    {
        foreach ($subjects as &$subject) {
            $subjectAverages = [];

            foreach ($studentsData as $studentData) {
                if (isset($studentData['averages'][$subject['id']])) {
                    $subjectAverages[] = $studentData['averages'][$subject['id']];
                }
            }

            $subject['class_average'] = !empty($subjectAverages) ?
                array_sum($subjectAverages) / count($subjectAverages) : 0;
            $subject['min_average'] = !empty($subjectAverages) ? min($subjectAverages) : 0;
            $subject['max_average'] = !empty($subjectAverages) ? max($subjectAverages) : 0;
        }

        return $subjects;
    }

    private function calculateAttendanceStatistics($studentsData, $subjects)
    {
        $totalAbsences = 0;
        $totalPossibleMarks = 0;

        foreach ($studentsData as $studentData) {
            foreach ($subjects as $subject) {
                foreach ($subject['evaluations'] as $evaluation) {
                    $totalPossibleMarks++;
                    if (isset($studentData['marks'][$subject['id']][$evaluation['id']])) {
                        if ($studentData['marks'][$subject['id']][$evaluation['id']]['is_absent'] ?? false) {
                            $totalAbsences++;
                        }
                    }
                }
            }
        }

        return [
            'absent' => $totalAbsences,
            'present' => $totalPossibleMarks - $totalAbsences,
            'total' => $totalPossibleMarks,
            'absence_rate' => $totalPossibleMarks > 0 ? ($totalAbsences / $totalPossibleMarks) * 100 : 0,
        ];
    }

    private function calculateStandardDeviation($array)
    {
        if (empty($array)) return 0;

        $n = count($array);
        $mean = array_sum($array) / $n;
        $carry = 0.0;

        foreach ($array as $val) {
            $d = ((float) $val) - $mean;
            $carry += $d * $d;
        }

        return sqrt($carry / $n);
    }

    private function calculateMedian($array)
    {
        if (empty($array)) return 0;

        sort($array);
        $count = count($array);
        $middle = floor(($count - 1) / 2);

        if ($count % 2) {
            return $array[$middle];
        } else {
            return ($array[$middle] + $array[$middle + 1]) / 2;
        }
    }

    public function show(Bulletin $bulletin)
    {
        $this->authorize('view-bulletin');

        try {
            return view('reports.bulletin-standard', [
                'bulletin' => $bulletin,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur affichage bulletin: '.$e->getMessage());
            return redirect()->back()->with('error', 'Impossible d\'afficher le bulletin.');
        }
    }

    public function downloadPDF(Bulletin $bulletin)
    {
        $this->authorize('view-bulletin');

        try {
            $bulletinType = $bulletin->type === 'apc' ? 'apc' : 'standard';
            $data = [
                'bulletin' => $bulletin,
                'results' => $bulletin->content,
            ];

            $view = $bulletinType === 'apc' ? 'reports.bulletin-apc' : 'reports.bulletin-standard';

            while (ob_get_level()) {
                ob_end_clean();
            }

            $pdf = Pdf::loadView($view, $data)
                ->setPaper('a4', 'portrait');

            $filename = "Bulletin_{$bulletin->student->matricule}_{$bulletin->term->name}.pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur téléchargement bulletin PDF: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Impossible de télécharger le bulletin.');
        }
    }

    /**
     * Archive & archived methods
     */
    public function archive(Classe $classe, Request $request)
    {
        $this->authorize('generate-reports');

        try {
            $termId = $request->input('term_id');
            $schoolYearId = $request->input('school_year_id');

            $bulletins = Bulletin::where([
                'class_id' => $classe->id,
                'term_id' => $termId,
                'school_year_id' => $schoolYearId,
            ])->get();

            if ($bulletins->isEmpty()) {
                return back()->with('warning', 'Aucun bulletin à archiver.');
            }

            $bulletins->each->update(['is_archived' => true]);

            return back()->with('success', count($bulletins) . ' bulletin(s) archivé(s).');

        } catch (\Exception $e) {
            Log::error('Erreur archivage: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur lors de l\'archivage.');
        }
    }

    public function archived(Request $request)
    {
        $this->authorize('view-reports');

        try {
            $bulletins = Bulletin::where('is_archived', true)
                ->with(['student.user', 'term', 'schoolYear', 'class'])
                ->paginate(20);

            return view('reports.archived-bulletins', compact('bulletins'));

        } catch (\Exception $e) {
            Log::error('Erreur affichage bulletins archivés: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Erreur.');
        }
    }
}
