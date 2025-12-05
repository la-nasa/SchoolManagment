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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\GeneralAverage;

class BulletinController extends Controller
{
    protected $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->middleware('auth');
        $this->calculationService = $calculationService;
    }


    public function generateForClass(Request $request, Classe $classe)
    {
        $this->authorize('generate-bulletins');

        $term = Term::findOrFail($request->input('term_id'));
        $schoolYear = SchoolYear::findOrFail($request->input('school_year_id'));
        $type = $request->input('type', 'standard'); // standard|apc

        try {
            // recalculer moyennes avant génération
            $this->calc->calculateAllSubjectAverages($classe, $term, $schoolYear);

            $students = $classe->students()->with('user')->get();

            DB::transaction(function () use ($students, $classe, $term, $schoolYear, $type) {
                foreach ($students as $student) {
                    $general = GeneralAverage::where([
                        'student_id' => $student->id,
                        'class_id' => $classe->id,
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
                            'name' => $student->user->first_name . ' ' . $student->user->last_name,
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

            // Retourner une vue listant les bulletins ou rediriger vers un téléchargement
            return redirect()->back()->with('success', 'Bulletins générés et enregistrés.');

        } catch (\Exception $e) {
            Log::error('Erreur génération bulletins: '.$e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération des bulletins.');
        }
    }


   public function generateStudentBulletin(Request $request, $studentId)
{
    $this->authorize('generate-reports');

    set_time_limit(120);

    try {
        Log::info("=== DÉBUT GÉNÉRATION BULLETIN ÉTUDIANT ===");

        // Récupérer l'étudiant
        $student = Student::find($studentId);
        if (!$student) {
            return back()->with('error', 'Élève non trouvé.');
        }

        if (!$student->class_id) {
            return back()->with('error', "Cet élève n'est pas assigné à une classe.");
        }

        // Valider les paramètres
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

        // Récupérer la classe
        $classe = Classe::find($student->class_id);
        if (!$classe) {
            return back()->with('error', "La classe de l'élève n'existe pas.");
        }

        $schoolSettings = SchoolSetting::first();

        Log::info("Étudiant: {$student->full_name}, Classe: {$classe->name}");

        // Calculer les moyennes
        Log::info("Calcul des moyennes...");
        $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
        $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

        // Calculer les résultats
        $results = $this->calculationService->calculateStudentResults(
            $student->id,
            $schoolYear->id,
            $term->id
        );

        if (empty($results['subject_results'])) {
            Log::warning("Aucune matière trouvée pour l'étudiant {$student->id}");
            return back()->with('error', 'Aucune donnée académique disponible.');
        }

        // Calculer les statistiques de classe
        $classStats = $this->calculationService->calculateClassStatistics(
            $classe->id,
            $term->id,
            $schoolYear->id
        );

        // Enregistrer le bulletin
        Bulletin::updateOrCreate(
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
                'generated_by' => Auth::id(),
                'generated_at' => now(),
                'type' => $bulletinType
            ]
        );

        // Préparer les données
        $data = [
            'student' => $student,
            'schoolYear' => $schoolYear,
            'term' => $term,
            'results' => $results,
            'settings' => $schoolSettings,
            'classStats' => $classStats,
            'classe' => $classe,
            'type' => $bulletinType,
            'examTypes' => \App\Models\ExamType::all()
        ];

        // Sélectionner la vue
        $viewName = $bulletinType === 'apc' ? 'reports.bulletin-apc' : 'reports.bulletin-standard';

        Log::info("Génération PDF avec vue: {$viewName}");

        // Générer le PDF avec options optimisées
        $pdf = Pdf::loadView($viewName, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'Arial',
                'enable_php' => false,
                'chroot' => [base_path(), storage_path()],
                'dpi' => 96,
                'enable_font_subsetting' => true,
                'compress' => true,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);

        $filename = "bulletin_{$student->matricule}_{$term->name}_{$schoolYear->year}.pdf";

        Log::info("=== BULLETIN GÉNÉRÉ AVEC SUCCÈS ===");

        // Retourner le PDF
        return $pdf->download($filename);

    } catch (\Exception $e) {
        Log::error('ERREUR GÉNÉRATION BULLETIN ÉTUDIANT: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
    }
}
public function generateStudentBulletinSimple(Request $request, $studentId)
{
    $this->authorize('generate-reports');
    set_time_limit(300);

    try {
        Log::info("=== DÉBUT GÉNÉRATION BULLETIN SIMPLE ===");
        
        // Récupérer l'étudiant avec les relations
        $student = Student::with(['class', 'class.teacher'])->find($studentId);
        if (!$student) {
            return back()->with('error', 'Élève non trouvé.');
        }

        if (!$student->class_id) {
            return back()->with('error', "Cet élève n'est pas assigné à une classe.");
        }

        // Valider les paramètres
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

        // Récupérer la classe
        $classe = Classe::with('teacher')->find($student->class_id);
        if (!$classe) {
            return back()->with('error', "La classe de l'élève n'existe pas.");
        }

        Log::info("Étudiant: {$student->full_name}, Classe: {$classe->name}");

        // Calculer les moyennes
        Log::info("Calcul des moyennes...");
        $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
        $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

        // Calculer les résultats
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

        // Calculer les statistiques de classe
        $classStats = $this->calculationService->calculateClassStatistics(
            $classe,
            $term,
            $schoolYear
        );

        if (empty($classStats) || !is_array($classStats)) {
            Log::warning('calculateClassStatistics a retourné des données invalides, utilisation de valeurs par défaut');
            $classStats = [
                'class_average' => 0,
                'total_students' => 0,
                'max_average' => 0,
                'min_average' => 0,
                'success_rate' => 0,
                'top_average' => 0,
                'bottom_average' => 0
            ];
        }

        // Enregistrer le bulletin
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

        // Préparer les données pour le PDF
        $data = [
            'student' => $student,
            'schoolYear' => $schoolYear,
            'term' => $term,
            'results' => $results, // Structure déjà correcte
            'settings' => $schoolSettings,
            'classStats' => $classStats,
            'classe' => $classe,
            'type' => $bulletinType,
            'examTypes' => \App\Models\ExamType::all(),
            'bulletin' => $bulletin,
            'competences' => $this->getCompetencesBySubject()
        ];

        // Sélectionner la vue
        $viewName = $bulletinType === 'apc' ? 'reports.bulletin-apc' : 'reports.bulletin-standard';

        Log::info("Génération PDF avec vue: {$viewName}");

        // Testez d'abord si la vue peut être rendue
        try {
            $html = view($viewName, $data)->render();
            Log::info("HTML généré avec succès: " . strlen($html) . " caractères");
        } catch (\Exception $e) {
            Log::error("Erreur rendu vue: " . $e->getMessage());
            // Utilisez une vue simplifiée en cas d'erreur
            $viewName = 'reports.simple-bulletin';
        }

        // Générer le PDF avec options optimisées
        $pdf = Pdf::loadView($viewName, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true, // Permettre les images
                'defaultFont' => 'DejaVu Sans', // Police compatible PDF
                'enable_php' => false,
                'dpi' => 96,
                'enable_font_subsetting' => true,
                'compress' => true,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);

        $filename = "bulletin_{$student->matricule}_{$term->name}_{$schoolYear->year}.pdf";

        Log::info("=== BULLETIN GÉNÉRÉ AVEC SUCCÈS ===");
        
        return $pdf->download($filename);

    } catch (\Exception $e) {
        Log::error('ERREUR GÉNÉRATION BULLETIN SIMPLE: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
    }
}
    /**
     * Générer les bulletins pour une classe entière
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

        // Charger les étudiants avec leurs moyennes
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

        // Calculer les moyennes
        Log::info('Calcul des moyennes...');
        $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
        $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

        // Créer un dossier temporaire
        $tempDir = storage_path('app/temp/bulletins_' . Str::random(20));
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $zip = new ZipArchive();
        $zipFilename = "bulletins_{$classe->name}_{$term->name}_{$schoolYear->year}.zip";
        $zipPath = $tempDir . '/' . $zipFilename;

        // Ouvrir le ZIP en mode création
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Impossible de créer le fichier ZIP");
        }

        $generatedCount = 0;
        $errors = [];

        foreach ($students as $student) {
            try {
                set_time_limit(30);

                // Calculer les résultats
                $results = $this->calculationService->calculateStudentResults(
                    $student->id,
                    $schoolYear->id,
                    $term->id
                );

                if (empty($results['subject_results'])) {
                    $errors[] = "{$student->matricule}: Aucune note trouvée";
                    continue;
                }

                // Calculer les statistiques de classe
                $classStats = $this->calculationService->calculateClassStatistics(
                    $classe->id,
                    $term->id,
                    $schoolYear->id
                );

                // Enregistrer le bulletin
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

                // Préparer les données pour le PDF
                $data = [
                    'student' => $student,
                    'schoolYear' => $schoolYear,
                    'term' => $term,
                    'results' => $results,
                    'settings' => $schoolSettings,
                    'classStats' => $classStats,
                    'classe' => $classe,
                    'type' => $bulletinType,
                    'examTypes' => \App\Models\ExamType::all()
                ];

                // Sélectionner la vue
                $viewName = $bulletinType === 'apc' ? 'reports.bulletin-apc' : 'reports.bulletin-standard';

                // Générer le PDF avec des options optimisées
                $pdf = Pdf::loadView($viewName, $data)
                    ->setPaper('a4', 'portrait')
                    ->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => false,
                        'defaultFont' => 'Arial',
                        'enable_php' => false,
                        'chroot' => [base_path(), storage_path()],
                        'dpi' => 96,
                        'enable_font_subsetting' => true,
                        'compress' => true,
                    ]);

                // Sauvegarder temporairement le PDF
                $tempPdfFilename = "bulletin_{$student->matricule}_{$term->name}.pdf";
                $tempPdfPath = $tempDir . '/' . $tempPdfFilename;

                // Sauvegarder le PDF dans un fichier temporaire
                $pdf->save($tempPdfPath);

                // Vérifier que le fichier a été créé
                if (!file_exists($tempPdfPath)) {
                    throw new \Exception("Impossible de créer le fichier PDF");
                }

                // Ajouter le fichier au ZIP
                if ($zip->addFile($tempPdfPath, $tempPdfFilename)) {
                    $generatedCount++;
                    Log::debug("Bulletin généré pour {$student->full_name}");
                } else {
                    throw new \Exception("Impossible d'ajouter le fichier au ZIP");
                }

            } catch (\Exception $e) {
                $studentName = $student ? $student->full_name : "Étudiant inconnu";
                $errors[] = "{$studentName}: " . $e->getMessage();
                Log::error("Erreur génération bulletin étudiant: " . $e->getMessage());
                continue;
            }
        }

        // Fermer le ZIP
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

        // Vérifier que le fichier ZIP existe et n'est pas vide
        if (!file_exists($zipPath) || filesize($zipPath) == 0) {
            File::deleteDirectory($tempDir);
            return back()->with('error', 'Le fichier ZIP généré est vide ou corrompu.');
        }

        // Retourner le fichier ZIP
        return response()
            ->download($zipPath, $zipFilename)
            ->deleteFileAfterSend(true)
            ->setPrivate()
            ->setMaxAge(0);

    } catch (\Exception $e) {
        Log::error('Erreur génération bulletins classe: ' . $e->getMessage());

        // Nettoyer les fichiers temporaires en cas d'erreur
        if ($tempDir && File::exists($tempDir)) {
            File::deleteDirectory($tempDir);
        }

        return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
    }
}

/**
 * Vérifier les données avant génération
 */
/**
 * Vérifier les données avant génération
 */
private function validateBulletinData($student, $term, $schoolYear)
{
    $errors = [];

    // Vérifier les notes
    $marksCount = Mark::where('student_id', $student->id)
        ->where('term_id', $term->id)
        ->where('school_year_id', $schoolYear->id)
        ->count();

    if ($marksCount === 0) {
        $errors[] = "Aucune note trouvée pour cet élève pour cette période.";
    }

    // Vérifier les moyennes
    $averagesCount = Average::where('student_id', $student->id)
        ->where('term_id', $term->id)
        ->where('school_year_id', $schoolYear->id)
        ->count();

    // Vérifier les évaluations
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
    /**
     * Méthode helper pour vérifier si l'utilisateur est professeur principal
     */
    private function isClassTeacher($user, $classId)
    {
        if (!$user->hasRole('teacher')) {
            return false;
        }

        // Récupérer l'ID du professeur
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            return false;
        }

        return \App\Models\TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->where('is_class_teacher', true)
            ->exists();
    }

    /**
     * Obtenir l'appréciation pour une note
     */
    private function getMarkAppreciation($mark)
    {
        if ($mark >= 18) return 'Excellent';
        if ($mark >= 16) return 'Très bien';
        if ($mark >= 14) return 'Bien';
        if ($mark >= 12) return 'Assez bien';
        if ($mark >= 10) return 'Passable';
        return 'Insuffisant';
    }


    /**
     * Générer les PV pour une classe
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

        // Charger les évaluations avec les étudiants
        $evaluations = Evaluation::where('class_id', $classe->id)
            ->where('term_id', $termId)
            ->where('school_year_id', $schoolYearId)
            ->with([
                'subject',
                'examType',
                'marks.student' // Charger les étudiants via les notes
            ])
            ->get();

        if ($evaluations->isEmpty()) {
            return back()->with('error', 'Aucune évaluation trouvée pour cette période');
        }

        // Créer un dossier temporaire
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

        // Charger tous les étudiants de la classe une seule fois
        $allStudents = $classe->students()->get()->keyBy('id');

        foreach ($evaluations as $evaluation) {
            try {
                // Préparer les données des étudiants avec leurs notes
                $studentsWithMarks = [];

                // Pour chaque étudiant de la classe
                foreach ($allStudents as $student) {
                    // Chercher la note pour cet étudiant dans cette évaluation
                    $mark = $evaluation->marks->firstWhere('student_id', $student->id);

                    $studentsWithMarks[] = [
                        'student' => $student,
                        'marks' => $mark ? $mark->marks : 0,
                        'is_absent' => $mark ? $mark->is_absent : false,
                        'appreciation' => $mark ? $this->getMarkAppreciation($mark->marks) : '-',
                        'mark' => $mark
                    ];
                }

                // Trier par nom
                usort($studentsWithMarks, function($a, $b) {
                    $nameA = $a['student']->last_name . ' ' . $a['student']->first_name;
                    $nameB = $b['student']->last_name . ' ' . $b['student']->first_name;
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

                $pdf = Pdf::loadView('reports.pv', $data)
                          ->setPaper('a4', 'landscape')
                          ->setOptions([
                              'isHtml5ParserEnabled' => true,
                              'isRemoteEnabled' => false,
                          ]);

                $pdfContent = $pdf->output();
                $filename = "PV_" . Str::slug($evaluation->subject->name ?? 'sujet') .
                           "_" . ($evaluation->exam_date->format('Y-m-d') ?? date('Y-m-d')) . ".pdf";

                if ($zip->addFromString($filename, $pdfContent)) {
                    $generatedCount++;
                }

            } catch (\Exception $e) {
                $errors[] = "Évaluation {$evaluation->subject->name}: " . $e->getMessage();
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
        return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
    }
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


    private function generateStandardBulletinPDF($student, $schoolYear, $term, $results, $schoolSettings, $bulletin, $classStats)
    {
        $examTypes = ExamType::where('category', 'sequence')->get();

        $data = [
            'student' => $student,
            'schoolYear' => $schoolYear,
            'term' => $term,
            'results' => $results,
            'settings' => $schoolSettings,
            'bulletin' => $bulletin,
            'classStats' => $classStats,
            'examTypes' => $examTypes,
        ];

        return Pdf::loadView('reports.bulletin-standard', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'Arial'
                  ]);
    }


    private function ensureTempDirectory()
    {
        $tempDir = storage_path('app/temp/');

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Vérifier que le répertoire est accessible en écriture
        if (!is_writable($tempDir)) {
            throw new \Exception("Le répertoire temporaire n'est pas accessible en écriture: " . $tempDir);
        }

        return $tempDir;
    }

    private function generateAPCBulletinPDF($student, $schoolYear, $term, $results, $schoolSettings, $bulletin, $classStats)
    {
        $data = [
            'student' => $student,
            'schoolYear' => $schoolYear,
            'term' => $term,
            'results' => $results,
            'settings' => $schoolSettings,
            'bulletin' => $bulletin,
            'classStats' => $classStats,
            'competences' => $this->getCompetencesBySubject(),
        ];

        return Pdf::loadView('reports.bulletin-apc', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'Arial'
                  ]);
    }

    private function generatePVPDF($evaluation, $schoolYear, $schoolSettings)
    {
        $studentsWithMarks = $evaluation->classe->students->map(function($student) use ($evaluation) {
            $mark = $evaluation->marks->firstWhere('student_id', $student->id);
            return [
                'student' => $student,
                'marks' => $mark ? $mark->marks : 0,
                'is_absent' => $mark ? $mark->is_absent : false,
                'appreciation' => $mark ? $mark->appreciation : '-'
            ];
        });

        $data = [
            'evaluation' => $evaluation,
            'schoolYear' => $schoolYear,
            'settings' => $schoolSettings,
            'studentsWithMarks' => $studentsWithMarks,
            'generatedBy' => Auth::user(),
        ];

        return Pdf::loadView('reports.pv', $data)
                  ->setPaper('a4', 'landscape')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                  ]);
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

            $pdf = Pdf::loadView($view, $data)
                ->setPaper('a4', 'portrait');

            $filename = "Bulletin_{$bulletin->student->matricule}_{$bulletin->term->name}.pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur téléchargement bulletin PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Impossible de télécharger le bulletin.');
        }
    }

    /**
     * Archiver les bulletins d'une classe
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
            Log::error('Erreur archivage: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'archivage.');
        }
    }

    /**
     * Liste des bulletins archivés
     */
    public function archived(Request $request)
    {
        $this->authorize('view-reports');

        try {
            $bulletins = Bulletin::where('is_archived', true)
                ->with(['student.user', 'term', 'schoolYear', 'class'])
                ->paginate(20);

            return view('reports.archived-bulletins', compact('bulletins'));

        } catch (\Exception $e) {
            Log::error('Erreur affichage bulletins archivés: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur.');
        }
    }

    /**
 * Générer un procès-verbal complet pour une classe
 */
public function generateClassReport(Classe $classe, Request $request)
{
    $this->authorize('generate-reports');

    $tempPdfPath = null;

    try {
        $termId = $request->input('term_id');
        $schoolYearId = $request->input('school_year_id');

        if (!$termId || !$schoolYearId) {
            return back()->with('error', 'Veuillez sélectionner un trimestre et une année scolaire.');
        }

        $term = Term::findOrFail($termId);
        $schoolYear = SchoolYear::findOrFail($schoolYearId);
        $schoolSettings = SchoolSetting::first();

        // 1. Récupérer les étudiants triés par ordre alphabétique
        $students = $classe->students()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Aucun étudiant dans cette classe.');
        }

        // 2. Calculer les moyennes si nécessaire
        Log::info('Calcul des moyennes pour le PV...');
        $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
        $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

        // 3. Récupérer toutes les matières avec leurs coefficients
        $subjects = Subject::where('is_active', true)
            ->with(['evaluations' => function($query) use ($classe, $term, $schoolYear) {
                $query->where('class_id', $classe->id)
                    ->where('term_id', $term->id)
                    ->where('school_year_id', $schoolYear->id);
            }])
            ->get()
            ->map(function($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'coefficient' => $subject->coefficient,
                    'evaluations' => $subject->evaluations->map(function($eval) {
                        return [
                            'id' => $eval->id,
                            'name' => $eval->title,
                            'max_marks' => $eval->max_marks,
                            'exam_date' => $eval->exam_date,
                        ];
                    }),
                ];
            })
            ->filter(function($subject) {
                return $subject['evaluations']->isNotEmpty();
            })
            ->values();

        // 4. Calculer le total des coefficients
        $totalCoefficients = $subjects->sum('coefficient');

        // 5. Préparer les données des étudiants
        $studentsData = [];

        foreach ($students as $student) {
            // Récupérer la moyenne générale
            $generalAverage = GeneralAverage::where([
                'student_id' => $student->id,
                'classe_id' => $classe->id,
                'term_id' => $term->id,
                'school_year_id' => $schoolYear->id,
            ])->first();

            $studentsData[] = [
                'student' => $student,
                'general_average' => $generalAverage ? $generalAverage->average : 0,
                'rank' => $generalAverage ? $generalAverage->rank : 0,
                'appreciation' => $generalAverage ? $generalAverage->appreciation : 'Non noté',
            ];
        }

        // 6. Classer les étudiants par moyenne générale
        usort($studentsData, function($a, $b) {
            return $b['general_average'] <=> $a['general_average'];
        });

        // 7. Calculer les statistiques de classe
        $classStatistics = $this->calculateClassStatisticsForPV($studentsData);

        // 8. Créer un fichier PDF temporaire
        $tempDir = storage_path('app/temp/pv_' . Str::random(20));
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $pdfFilename = "PV_{$classe->name}_{$term->name}_{$schoolYear->year}.pdf";
        $tempPdfPath = $tempDir . '/' . $pdfFilename;

        // 9. Générer le PDF avec des options optimisées
        $pdf = Pdf::loadView('reports.class-pv', [
            'classe' => $classe,
            'term' => $term,
            'schoolYear' => $schoolYear,
            'settings' => $schoolSettings,
            'students' => $studentsData,
            'subjects' => $subjects,
            'totalCoefficients' => $totalCoefficients,
            'classStatistics' => $classStatistics,
            'observations' => $request->input('observations', ''),
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'Arial',
            'enable_php' => false,
            'chroot' => [base_path(), storage_path()],
            'dpi' => 96,
            'enable_font_subsetting' => true,
            'compress' => true,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);

        // Sauvegarder le PDF
        $pdf->save($tempPdfPath);

        // Vérifier que le fichier a été créé
        if (!file_exists($tempPdfPath) || filesize($tempPdfPath) == 0) {
            throw new \Exception("Le fichier PDF généré est vide ou corrompu.");
        }

        Log::info("PDF généré avec succès: " . filesize($tempPdfPath) . " octets");

        // 10. Retourner le PDF
        return response()
            ->download($tempPdfPath, $pdfFilename)
            ->deleteFileAfterSend(true)
            ->setPrivate()
            ->setMaxAge(0);

    } catch (\Exception $e) {
        Log::error('Erreur génération PV classe: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        // Nettoyer les fichiers temporaires en cas d'erreur
        if ($tempPdfPath && File::exists(dirname($tempPdfPath))) {
            File::deleteDirectory(dirname($tempPdfPath));
        }

        return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
    }
}

/**
 * Calculer les statistiques de classe pour le PV
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

/**
 * Calculer les statistiques par matière
 */
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

/**
 * Calculer les statistiques de présence
 */
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

/**
 * Calculer l'écart-type
 */
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

/**
 * Calculer la médiane
 */
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

 public function generateBulletinForStudent(Student $student, Request $request)
    {
        $this->authorize('generate-reports');

        try {
            // Vérification approfondie de l'étudiant
            if (!$student) {
                return back()->with('error', 'Élève non trouvé.');
            }

            if (!$student->hasClass()) {
                return back()->with('error', "Cet élève n'est pas assigné à une classe. Veuillez d'abord assigner une classe à cet élève.");
            }

            // Valider les paramètres
            $request->validate([
                'term_id' => 'required|exists:terms,id',
                'school_year_id' => 'required|exists:school_years,id',
                'type' => 'nullable|in:standard,apc'
            ]);

            // Récupérer les paramètres
            $termId = $request->input('term_id');
            $schoolYearId = $request->input('school_year_id');
            $bulletinType = $request->input('type', 'standard');

            // Rediriger vers la méthode principale
            return redirect()->route('admin.bulletins.generate-student', [
                'student' => $student->id,
                'term_id' => $termId,
                'school_year_id' => $schoolYearId,
                'type' => $bulletinType
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }



// Dans BulletinController.php, ajouter :
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


}
