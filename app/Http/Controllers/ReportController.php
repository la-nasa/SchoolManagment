<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classe;
use App\Models\Evaluation;
use App\Models\Term;
use App\Models\SchoolYear;
use App\Models\SchoolSetting;
use App\Models\ExamType;
use App\Models\Sequence;
use App\Models\Bulletin;
use App\Services\MarkCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    protected $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->middleware('auth');
        $this->calculationService = $calculationService;
    }

    public function bulletinType(Classe $classe)
    {
        $this->authorize('generate-reports');

        $terms = Term::orderBy('order')->get();
        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('reports.type', compact('classe', 'terms', 'schoolYears'));
    }

    /**
     * Générer un bulletin individuel
     */
    public function generateBulletin(Student $student, Request $request)
    {
        $this->authorize('generate-reports');

        $termId = $request->get('term_id', Term::current()->id);
        $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);

        $term = Term::findOrFail($termId);
        $schoolYear = SchoolYear::findOrFail($schoolYearId);
        $schoolSettings = SchoolSetting::getSettings();

        $user = Auth::user();
        if ($user->hasRole('teacher') && !$this->isClassTeacher($user, $student->class_id)) {
            abort(403, 'Accès non autorisé à ce bulletin');
        }

        $student->load(['class.teacher']);

        $results = $this->calculationService->calculateStudentResults($student->id, $schoolYearId, $termId);
        $classStats = $this->calculationService->calculateClassStatistics($student->class_id, $schoolYearId, $termId);

        // Vérifier que les résultats existent
        if (!$results || empty($results)) {
            return back()->with('error', 'Aucun résultat académique disponible pour cet élève.');
        }

        // Assurer que rank a une valeur par défaut
        $rank = $results['rank'] ?? 0;
        if ($rank === null || $rank === '') {
            $rank = 0;
        }

        $bulletinType = $request->get('type', 'standard');

        $bulletin = Bulletin::updateOrCreate(
            [
                'student_id' => $student->id,
                'school_year_id' => $schoolYearId,
                'term_id' => $termId
            ],
            [
                'class_id' => $student->class_id,
                'average' => $results['general_average'] ?? 0,
                'rank' => $rank,
                'appreciation' => $this->getAppreciation($results['general_average'] ?? 0),
                'head_teacher_comment' => $request->head_teacher_comment,
                'principal_comment' => $request->principal_comment,
                'generated_by' => Auth::id(),
                'generated_at' => now()
            ]
        );

        if ($bulletinType === 'apc') {
            $pdf = $this->generateAPCBulletinPDF($student, $schoolYear, $term, $results, $schoolSettings, $bulletin, $classStats);
        } else {
            $pdf = $this->generateStandardBulletinPDF($student, $schoolYear, $term, $results, $schoolSettings, $bulletin, $classStats);
        }

        $filename = "bulletin_{$student->matricule}_{$term->name}_{$schoolYear->year}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Générer les bulletins pour toute une classe
     */
    public function generateClassBulletins(Classe $classe, Request $request)
    {
        $this->authorize('generate-reports');

        $termId = $request->get('term_id', Term::current()->id);
        $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);
        $bulletinType = $request->get('type', 'standard');

        $term = Term::findOrFail($termId);
        $schoolYear = SchoolYear::findOrFail($schoolYearId);
        $schoolSettings = SchoolSetting::getSettings();

        // Vérifier les permissions
        $user = Auth::user();
        if ($user->hasRole('teacher') && !$this->isClassTeacher($user, $classe->id)) {
            abort(403, 'Accès non autorisé à cette classe');
        }

        $students = $classe->students()->with(['user'])->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Aucun étudiant trouvé dans cette classe.');
        }

        try {
            $zipPath = 'temp/bulletins_' . Str::random(10) . '.zip';
            $zip = new ZipArchive();

            if ($zip->open(Storage::path($zipPath), ZipArchive::CREATE) !== true) {
                return back()->with('error', 'Impossible de créer le ZIP');
            }

            // Recalculer les moyennes avant de générer les bulletins
            $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
            $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

            foreach ($students as $student) {
                $results = $this->calculationService->calculateStudentResults($student->id, $schoolYearId, $termId);
                $classStats = $this->calculationService->calculateClassStatistics($classe, $term, $schoolYear);

                if (!$results || empty($results)) {
                    continue;
                }

                $rank = $results['rank'] ?? 0;
                if ($rank === null || $rank === '') {
                    $rank = 0;
                }

                Bulletin::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'school_year_id' => $schoolYearId,
                        'term_id' => $termId
                    ],
                    [
                        'class_id' => $classe->id,
                        'average' => $results['general_average'] ?? 0,
                        'rank' => (int)$rank,
                        'appreciation' => $this->getAppreciation($results['general_average'] ?? 0),
                        'generated_by' => Auth::id(),
                        'generated_at' => now()
                    ]
                );

                $pdf = $bulletinType === 'apc'
                    ? $this->generateAPCBulletin($student, $schoolYear, $term, $results, $classStats)
                    : $this->generateStandardBulletin($student, $schoolYear, $term, $results, $classStats);

                $pdfContent = $pdf->output();
                $filename = "Bulletin_{$student->matricule}_{$student->user->last_name}_{$term->name}.pdf";
                $zip->addFromString($filename, $pdfContent);
            }

            $zip->close();

            $zipFilename = "Bulletins_{$classe->name}_{$bulletinType}_{$term->name}_{$schoolYear->year}.zip";

            return response()->download(Storage::path($zipPath), $zipFilename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            if (isset($zip)) {
                $zip->close();
            }
            if (isset($zipPath)) {
                Storage::delete($zipPath);
            }
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

        // Générer le premier bulletin pour démonstration
        // $firstStudent = $students->first();
        // $results = $this->calculationService->calculateStudentResults($firstStudent->id, $schoolYearId, $termId);
        // $classStats = $this->calculationService->calculateClassStatistics($classe->id, $schoolYearId, $termId);

        // if ($bulletinType === 'apc') {
        //     $pdf = $this->generateAPCBulletinPDF($firstStudent, $schoolYear, $term, $results, $schoolSettings, null, $classStats);
        // } else {
        //     $pdf = $this->generateStandardBulletinPDF($firstStudent, $schoolYear, $term, $results, $schoolSettings, null, $classStats);
        // }

        // $filename = "bulletins_classe_{$classe->full_name}_{$term->name}_{$schoolYear->year}.pdf";

        // return $pdf->download($filename);


    /**
     * Générer un procès-verbal
     */
    public function generatePV(Evaluation $evaluation, Request $request)
    {
        $this->authorize('generate-reports');

        $sequenceId = $request->get('sequence_id');

        $evaluation->load([
            'classe.students.user',
            'subject',
            'examType',
            'marks.student.user'
        ]);

        $schoolYear = SchoolYear::current();
        $schoolSettings = SchoolSetting::getSettings();

        // Vérifier les permissions
        $user = Auth::user();
        if ($user->hasRole('teacher') && !$this->teachesSubjectInClass($user, $evaluation->subject_id, $evaluation->class_id)) {
            abort(403, 'Accès non autorisé à cette évaluation');
        }

        // Préparer les données pour le PV
        $studentsWithMarks = $evaluation->classe->students->map(function($student) use ($evaluation) {
            $mark = $evaluation->marks->where('student_id', $student->id)->first();

            return [
                'student' => $student,
                'mark' => $mark,
                'is_absent' => $mark ? $mark->is_absent : true,
                'marks' => $mark && !$mark->is_absent ? $mark->marks : 'Absent',
                'appreciation' => $mark ? $this->getMarkAppreciation($mark->marks, $evaluation->max_mark) : 'Absent'
            ];
        })->sortBy('student.user.last_name');

        $pdf = PDF::loadView('reports.pv', [
            'evaluation' => $evaluation,
            'studentsWithMarks' => $studentsWithMarks,
            'schoolYear' => $schoolYear,
            'settings' => $schoolSettings,
            'sequence' => $sequenceId ? Sequence::find($sequenceId) : null,
            'generatedBy' => $user
        ])->setPaper('a4', 'landscape')
          ->setOptions([
              'isHtml5ParserEnabled' => true,
              'isRemoteEnabled' => true,
              'defaultFont' => 'Arial'
          ]);

        $sequenceText = $sequenceId ? '_sequence_' . Sequence::find($sequenceId)->name : '';
        $filename = "pv_{$evaluation->classe->full_name}_{$evaluation->examType->name}{$sequenceText}.pdf";

        return $pdf->download($filename);
    }

    public function generateClassPV(Classe $classe, Request $request)
    {
        $this->authorize('generate-reports');

        $termId = $request->get('term_id', Term::current()->id);
        $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);

        $term = Term::findOrFail($termId);
        $schoolYear = SchoolYear::findOrFail($schoolYearId);

        $evaluations = Evaluation::where('class_id', $classe->id)
            ->where('term_id', $termId)
            ->where('school_year_id', $schoolYearId)
            ->with(['subject', 'marks.student.user', 'examType'])
            ->get();

        if ($evaluations->isEmpty()) {
            return back()->with('error', 'Aucune évaluation trouvée');
        }

        try {
            $zipPath = 'temp/pv_' . Str::random(10) . '.zip';
            $zip = new ZipArchive();

            if ($zip->open(Storage::path($zipPath), ZipArchive::CREATE) !== true) {
                return back()->with('error', 'Impossible de créer le ZIP');
            }

            foreach ($evaluations as $evaluation) {
                $pdf = $this->generatePV($evaluation, $schoolYear);
                $pdfContent = $pdf->output();
                $filename = "PV_{$evaluation->subject->name}_{$evaluation->evaluation_date->format('d-m-Y')}.pdf";
                $zip->addFromString($filename, $pdfContent);
            }

            $zip->close();

            $zipFilename = "PV_{$classe->name}_{$term->name}_{$schoolYear->year}.zip";

            return response()->download(Storage::path($zipPath), $zipFilename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            if (isset($zip)) {
                $zip->close();
            }
            if (isset($zipPath)) {
                Storage::delete($zipPath);
            }
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

 /**
 * Rapport général de l'école
 */
public function schoolReport(Request $request)
{
    $this->authorize('view-reports');

    $termId = $request->get('term_id', Term::current()->id);
    $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);

    $term = Term::findOrFail($termId);
    $schoolYear = SchoolYear::findOrFail($schoolYearId);

    // Calculer les statistiques de l'école
    $schoolStats = $this->calculationService->calculateSchoolStatistics($term, $schoolYear);

    // Récupérer les classes avec leurs statistiques
    $classes = Classe::with(['teacher', 'students'])
        ->get()
        ->map(function($classe) use ($schoolYear, $term) {
            try {
                $stats = $this->calculationService->calculateClassStatistics($classe, $term, $schoolYear);

                return [
                    'classe' => $classe,
                    'stats' => $stats ?? [
                        'average' => 0,
                        'success_rate' => 0,
                        'total_students' => 0
                    ]
                ];
            } catch (\Exception $e) {
                \Log::error("Erreur calcul stats classe {$classe->id}: " . $e->getMessage());

                return [
                    'classe' => $classe,
                    'stats' => [
                        'average' => 0,
                        'success_rate' => 0,
                        'total_students' => 0
                    ]
                ];
            }
        })
        ->filter(function($classData) {
            // Filtrer les classes qui ont des statistiques valides
            return $classData['stats']['total_students'] > 0;
        });

    // Statistiques par défaut si non disponibles
    $stats = $schoolStats ?? [
        'school_average' => 0,
        'success_rate' => 0,
        'total_students' => 0,
        'top_10' => [],
        'bottom_10' => [],
        'class_statistics' => [],
    ];

    if ($request->has('export')) {
        $pdf = Pdf::loadView('reports.school-pdf', [
            'schoolStats' => $stats,
            'classes' => $classes,
            'term' => $term,
            'schoolYear' => $schoolYear
        ])->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        return $pdf->download("rapport-ecole-{$term->name}-{$schoolYear->year}.pdf");
    }

    return view('reports.school', compact('stats', 'classes', 'term', 'schoolYear'));
}
    /**
     * Rapport de performance (Top 10 / Bottom 10)
     */
    public function performanceReport(Request $request)
    {
        $this->authorize('view-reports');

        $termId = $request->get('term_id', Term::current()->id);
        $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);

        $term = Term::findOrFail($termId);
        $schoolYear = SchoolYear::findOrFail($schoolYearId);

        $schoolStats = $this->calculationService->calculateSchoolStatistics($term, $schoolYear);

          $stats = $schoolStats ?? [
            'school_average' => 0,
            'success_rate' => 0,
            'total_students' => 0,
            'top_10' => [],
            'bottom_10' => [],
        ];
        // Top 10 et bottom 10 des élèves
        $topStudents = method_exists($this->calculationService, 'getTopStudents')
        ? $this->calculationService->getTopStudents(10, $schoolYearId, $termId)
        : collect();

        $bottomStudents = method_exists($this->calculationService, 'getBottomStudents')
        ? $this->calculationService->getBottomStudents(10, $schoolYearId, $termId)
        : collect();
        // $topStudents = method_exists($this->calculationService, 'getTopStudents')
        //     ? $this->calculationService->getTopStudents(10, $schoolYearId, $termId)
        //     : [];

        // $bottomStudents = method_exists($this->calculationService, 'getBottomStudents')
        //     ? $this->calculationService->getBottomStudents(10, $schoolYearId, $termId)
        //     : [];

        if ($request->has('export')) {
            $pdf = Pdf::loadView('reports.performance', [
                'schoolStats' => $schoolStats,
                'topStudents' => $topStudents,
                'bottomStudents' => $bottomStudents,
                'term' => $term,
                'schoolYear' => $schoolYear
            ])->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true
            ]);

            return $pdf->download("rapport-performance-{$term->name}-{$schoolYear->year}.pdf");
        }

        return view('reports.performance', compact('schoolStats', 'topStudents', 'bottomStudents', 'term', 'schoolYear'));
    }

    /**
     * Rapport détaillé d'une classe
     */
    public function classReport(Classe $classe, Request $request)
    {
        $this->authorize('view-reports');

        $termId = $request->get('term_id', Term::current()->id);
        $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);

        $term = Term::findOrFail($termId);
        $schoolYear = SchoolYear::findOrFail($schoolYearId);

        $classStats = $this->calculationService->calculateClassStatistics($classe->id, $schoolYearId, $termId);

        $students = $classe->students()->with(['user'])->get()->map(function($student) use ($schoolYearId, $termId) {
            $results = $this->calculationService->calculateStudentResults($student->id, $schoolYearId, $termId);
            return [
                'student' => $student,
                'results' => $results
            ];
        })->sortByDesc('results.general_average');

        if ($request->has('export')) {
            $pdf = Pdf::loadView('reports.class-report', [
                'classe' => $classe,
                'classStats' => $classStats,
                'students' => $students,
                'term' => $term,
                'schoolYear' => $schoolYear
            ])->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true
            ]);

            return $pdf->download("rapport-classe-{$classe->full_name}-{$term->name}.pdf");
        }

        return view('reports.class', compact('classe', 'classStats', 'students', 'term', 'schoolYear'));
    }

    /**
     * Rapport des enseignants
     */
    public function teachersReport(Request $request)
    {
        $this->authorize('view-reports');

        $termId = $request->get('term_id', Term::current()->id);
        $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);

        $term = Term::findOrFail($termId);
        $schoolYear = SchoolYear::findOrFail($schoolYearId);

        $teachers = \App\Models\User::role(['enseignant', 'enseignant titulaire'])
            ->with(['teacherAssignments' => function($query) use ($schoolYearId) {
                $query->where('school_year_id', $schoolYearId)
                      ->with(['classe', 'subject']);
            }])
            ->get()
            ->map(function($teacher) use ($term, $schoolYearId) {
                $assignments = $teacher->teacherAssignments;

                $completionStats = $this->calculationService->calculateTeacherCompletionRate(
                    $teacher->id,
                    $schoolYearId,
                    $term->id
                );

                return [
                    'teacher' => $teacher,
                    'assignments' => $assignments,
                    'completion_stats' => $completionStats,
                    'performance_stats' => $this->calculationService->calculateTeacherPerformance(
                        $teacher->id,
                        $schoolYearId,
                        $term->id
                    )
                ];
            });

        if ($request->has('export')) {
            $pdf = Pdf::loadView('reports.teachers', [
                'teachers' => $teachers,
                'term' => $term,
                'schoolYear' => $schoolYear
            ])->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true
            ]);

            return $pdf->download("rapport-enseignants-{$term->name}.pdf");
        }

        return view('reports.teachers', compact('teachers', 'term', 'schoolYear'));
    }

    /**
     * Bulletins archivés
     */
    public function archivedBulletins(Request $request)
    {
        $this->authorize('view-reports');

        $schoolYearId = $request->get('school_year_id');
        $termId = $request->get('term_id');
        $classId = $request->get('class_id');

        $query = Bulletin::with(['student.user', 'classe', 'schoolYear', 'term', 'generatedBy'])
                        ->orderBy('generated_at', 'desc');

        if ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        }

        if ($termId) {
            $query->where('term_id', $termId);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $bulletins = $query->paginate(20);

        $schoolYears = SchoolYear::orderBy('year', 'desc')->get();
        $terms = Term::all();
        $classes = Classe::all();

        return view('reports.archived-bulletins', compact('bulletins', 'schoolYears', 'terms', 'classes'));
    }

    /**
     * Méthodes privées pour la génération de PDF
     */

    private function generateStandardBulletinPDF($student, $schoolYear, $term, $results, $schoolSettings, $bulletin, $classStats)
    {
        $data = [
            'student' => $student,
            'schoolYear' => $schoolYear,
            'term' => $term,
            'results' => $results,
            'settings' => $schoolSettings,
            'bulletin' => $bulletin,
            'classStats' => $classStats,
            'examTypes' => ExamType::where('id' )->get()
            // 'sequences' => Sequence::where('term_id', $term->id)->get()
        ];

        return PDF::loadView('reports.bulletin-standard', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'Arial'
                  ]);
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
            // 'sequences' => Sequence::where('term_id', $term->id)->get()
        ];

        return PDF::loadView('reports.bulletin-apc', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'Arial'
                  ]);
    }

    /**
     * Méthodes utilitaires
     */

    private function isClassTeacher($user, $classId)
    {
        return $user->teacherAssignments()
            ->where('class_id', $classId)
            ->where('is_class_teacher', true)
            ->exists();
    }

    private function teachesSubjectInClass($user, $subjectId, $classId)
    {
        return $user->teacherAssignments()
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();
    }

    private function getAppreciation($average)
    {
        if ($average >= 16) return 'Très Bien';
        if ($average >= 14) return 'Bien';
        if ($average >= 12) return 'Assez Bien';
        if ($average >= 10) return 'Passable';
        if ($average >= 8) return 'Insuffisant';
        return 'Très Insuffisant';
    }

    private function getMarkAppreciation($mark, $maxMark = 20)
    {
        $percentage = ($mark / $maxMark) * 100;

        if ($percentage >= 80) return 'Excellent';
        if ($percentage >= 70) return 'Très Bien';
        if ($percentage >= 60) return 'Bien';
        if ($percentage >= 50) return 'Assez Bien';
        if ($percentage >= 40) return 'Passable';
        return 'Insuffisant';
    }

    private function getCompetencesBySubject()
    {
        return [
            'Français' => [
                'Produire à l\'écrit, un texte descriptif et, à l\'oral, un commentaire de l\'image.',
                'Produire, à l\'écrit, un texte narratif et, à l\'oral, un compte rendu oral.'
            ],
            'Mathématiques' => [
                'Résoudre des situations problèmes relatives à l\'arithmétique, aux nombres réels, aux propriétés de Thalès et à la trigonométrie dans le triangle rectangle.',
                'Communiquer à l\'aide du langage mathématique dans des situations relatives aux notions précédemment étudiées.'
            ],
            'Anglais' => [
                'Use appropriate language resources to listen, speak, read and write about national integration; diversity acceptance.',
                'Use appropriate language resources to listen, speak, read and write about consumption habits and how they impact economic and social life.'
            ],
            'Histoire-Géographie' => [
                'Analyser les grandes périodes historiques et leurs impacts sur la société actuelle.',
                'Comprendre les enjeux géographiques et environnementaux du monde contemporain.'
            ],
            'Sciences' => [
                'Appliquer la démarche scientifique pour résoudre des problèmes concrets.',
                'Comprendre les principes fondamentaux de la physique et de la chimie.'
            ]
        ];
    }

    /**
 * Aperçu du bulletin (version web)
 */
public function previewBulletin(Student $student, Request $request)
{
    $this->authorize('generate-reports');

    $termId = $request->get('term_id', Term::current()->id);
    $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);

    $term = Term::findOrFail($termId);
    $schoolYear = SchoolYear::findOrFail($schoolYearId);

    $results = $this->calculationService->calculateStudentResults($student->id, $schoolYearId, $termId);
    $classStats = $this->calculationService->calculateClassStatistics($student->class_id, $schoolYearId, $termId);

    return view('reports.preview-bulletin', compact('student', 'term', 'schoolYear', 'results', 'classStats'));
}

/**
 * Aperçu du rapport de classe (version web)
 */
public function previewClassReport(Classe $classe, Request $request)
{
    $this->authorize('view-reports');

    $termId = $request->get('term_id', Term::current()->id);
    $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);

    $term = Term::findOrFail($termId);
    $schoolYear = SchoolYear::findOrFail($schoolYearId);

    $classStats = $this->calculationService->calculateClassStatistics($classe->id, $schoolYearId, $termId);
    $students = $classe->students()->with(['user'])->get()->map(function($student) use ($schoolYearId, $termId) {
        $results = $this->calculationService->calculateStudentResults($student->id, $schoolYearId, $termId);
        return [
            'student' => $student,
            'results' => $results
        ];
    })->sortByDesc('results.general_average');

    return view('reports.preview-class', compact('classe', 'classStats', 'students', 'term', 'schoolYear'));
}
}
