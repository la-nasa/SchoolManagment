<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Term;
use App\Models\SchoolYear;
use App\Models\Bulletin;
use App\Models\SchoolSetting;
use App\Services\MarkCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;

class BulletinController extends Controller
{
    protected $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->middleware('auth');
        $this->calculationService = $calculationService;
    }

    /**
     * Générer les bulletins pour une classe entière
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

        $user = Auth::user();
        if ($user->hasRole('teacher') && !$this->isClassTeacher($user, $classe->id)) {
            abort(403, 'Accès non autorisé');
        }

        $students = $classe->students()->with(['user'])->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Aucun étudiant dans cette classe');
        }

        // Créer un dossier temporaire
        $zipPath = 'temp/bulletins_' . Str::random(10) . '.zip';
        $zip = new ZipArchive();

        if ($zip->open(Storage::path($zipPath), ZipArchive::CREATE) !== true) {
            return back()->with('error', 'Impossible de créer le ZIP');
        }

        try {
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

                // Créer ou mettre à jour le bulletin
                Bulletin::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'school_year_id' => $schoolYearId,
                        'term_id' => $termId
                    ],
                    [
                        'class_id' => $classe->id,
                        'average' => $results['general_average'] ?? 0,
                        'rank' => $rank,
                        'appreciation' => $this->getAppreciation($results['general_average'] ?? 0),
                        'generated_by' => Auth::id(),
                        'generated_at' => now()
                    ]
                );

                // Générer le PDF
                if ($bulletinType === 'apc') {
                    $pdf = $this->generateAPCBulletinPDF($student, $schoolYear, $term, $results, $schoolSettings, null, $classStats);
                } else {
                    $pdf = $this->generateStandardBulletinPDF($student, $schoolYear, $term, $results, $schoolSettings, null, $classStats);
                }

                $pdfContent = $pdf->output();
                $filename = "bulletin_{$student->matricule}_{$term->name}.pdf";
                $zip->addFromString($filename, $pdfContent);
            }

            $zip->close();

            $zipFilename = "Bulletins_{$classe->name}_{$bulletinType}_{$term->name}_{$schoolYear->year}.zip";

            return response()->download(Storage::path($zipPath), $zipFilename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            $zip->close();
            Storage::delete($zipPath);
            return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Générer les PV pour une classe
     */
    public function generateClassPV(Classe $classe, Request $request)
    {
        $this->authorize('generate-reports');

        $termId = $request->get('term_id', Term::current()->id);
        $schoolYearId = $request->get('school_year_id', SchoolYear::current()->id);

        $term = Term::findOrFail($termId);
        $schoolYear = SchoolYear::findOrFail($schoolYearId);
        $schoolSettings = SchoolSetting::getSettings();

        $evaluations = $classe->evaluations()
            ->where('term_id', $termId)
            ->where('school_year_id', $schoolYearId)
            ->with(['subject', 'marks.student.user', 'examType'])
            ->get();

        if ($evaluations->isEmpty()) {
            return back()->with('error', 'Aucune évaluation trouvée');
        }

        $zipPath = 'temp/pv_' . Str::random(10) . '.zip';
        $zip = new ZipArchive();

        if ($zip->open(Storage::path($zipPath), ZipArchive::CREATE) !== true) {
            return back()->with('error', 'Impossible de créer le ZIP');
        }

        try {
            foreach ($evaluations as $evaluation) {
                $pdf = $this->generatePVPDF($evaluation, $schoolYear, $schoolSettings);
                $pdfContent = $pdf->output();
                $filename = "PV_{$evaluation->subject->name}_{$evaluation->evaluation_date->format('d-m-Y')}.pdf";
                $zip->addFromString($filename, $pdfContent);
            }

            $zip->close();

            $zipFilename = "PV_{$classe->name}_{$term->name}_{$schoolYear->year}.zip";

            return response()->download(Storage::path($zipPath), $zipFilename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            $zip->close();
            Storage::delete($zipPath);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
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

    private function getAppreciation($average)
    {
        if ($average >= 16) return 'Excellent';
        if ($average >= 14) return 'Bien';
        if ($average >= 12) return 'Assez bien';
        if ($average >= 10) return 'Passable';
        if ($average >= 8) return 'Insuffisant';
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

    private function isClassTeacher($user, $classId)
    {
        return $user->teacherAssignments()
            ->where('class_id', $classId)
            ->where('is_class_teacher', true)
            ->exists();
    }
}
