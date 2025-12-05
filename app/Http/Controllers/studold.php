<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classe;
use App\Models\SchoolYear;
use App\Models\Term;
use App\Services\MarkCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;


class StudentController extends Controller
{
    public function __construct(MarkCalculationService $calculationService)
    {
        $this->middleware('auth');
        $this->calculationService = $calculationService;
    }
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Student::with(['class', 'schoolYear']);

        // Filtres
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('last_name')->paginate(25);
        $classes = Classe::active()->get();
        $currentSchoolYear = SchoolYear::current();

        return view('students.index', compact('students', 'classes', 'currentSchoolYear'));
    }

    public function create()
    {
        $this->authorize('create-students');

        $classes = Classe::active()->get();
        $schoolYears = SchoolYear::all();
        $currentSchoolYear = SchoolYear::current();

        return view('students.create', compact('classes', 'schoolYears', 'currentSchoolYear'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-students');

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:M,F',
            'birth_place' => 'nullable|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'school_year_id' => 'required|exists:school_years,id',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Générer le matricule
        $validated['matricule'] = Student::generateMatricule();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        Student::create($validated);

        return redirect()->route('admin.students.index')
            ->with('success', 'Élève créé avec succès.');
    }

   public function show(Student $student)
{
    $this->authorize('view-students');

    try {
        // Load all necessary relationships with proper relationship names
        $student->load([
            'class', // Use 'class' not 'classe'
            'schoolYear',
            'marks' => function($query) {
                $query->with(['evaluation' => function($q) {
                    $q->with(['subject', 'examType']);
                }, 'subject'])
                ->orderBy('created_at', 'desc');
            },
            'averages' => function($query) {
                $query->with(['subject', 'term', 'schoolYear'])
                      ->orderBy('term_id')
                      ->orderBy('subject_id');
            },
            'generalAverages' => function($query) {
                $query->with(['term', 'schoolYear'])
                      ->orderBy('term_id');
            }
        ]);

        // Check if the student has a class
        if (!$student->class) {
            return view('students.show', compact('student', 'terms', 'schoolYears', 'currentTerm', 'currentSchoolYear'))
                ->with('warning', 'Cet élève n\'est pas assigné à une classe.');
        }

        // Calculate age if birth date is available
        if ($student->birth_date) {
            $student->age = $student->birth_date->age;
        }

        // Load additional data for the modal
        $terms = Term::all();
        $schoolYears = SchoolYear::all();
        $currentTerm = Term::current()->first();
        $currentSchoolYear = SchoolYear::current();

        return view('students.show', compact(
            'student',
            'terms',
            'schoolYears',
            'currentTerm',
            'currentSchoolYear'
        ));

    } catch (\Exception $e) {
        Log::error('Erreur affichage étudiant: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return redirect()->route('admin.students.index')
            ->with('error', 'Erreur lors du chargement des détails de l\'élève. Détails: ' . $e->getMessage());
    }
}

    public function edit(Student $student)
    {
        $this->authorize('edit-students');

        $classes = Classe::active()->get();
        $schoolYears = SchoolYear::all();

        return view('students.edit', compact('student', 'classes', 'schoolYears'));
    }

    public function update(Request $request, Student $student)
    {
        $this->authorize('edit-students');

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:M,F',
            'birth_place' => 'nullable|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'school_year_id' => 'required|exists:school_years,id',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($student->photo) {
                \Storage::disk('public')->delete($student->photo);
            }
            $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student->update($validated);

        return redirect()->route('admin.students.index')
            ->with('success', 'Élève mis à jour avec succès.');
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete-students');

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Élève supprimé avec succès.');
    }

  
    
   public function generateBulletin(Student $student, Request $request)
    {
        $this->authorize('generate-reports');

        try {
            // Valider les paramètres
            $request->validate([
                'term_id' => 'required|exists:terms,id',
                'school_year_id' => 'required|exists:school_years,id',
                'type' => 'nullable|in:standard,apc'
            ]);

            $termId = $request->input('term_id');
            $schoolYearId = $request->input('school_year_id');
            $bulletinType = $request->input('type', 'standard');

            // Rediriger vers le contrôleur BulletinController
            // return redirect()->route('admin.bulletins.generate-student-simple', [
            //     'student' => $student->id,
            //     'term_id' => $termId,
            //     'school_year_id' => $schoolYearId,
            //     'type' => $bulletinType
            // ]);

        } catch (\Exception $e) {
            Log::error('Erreur redirection génération bulletin: ' . $e->getMessage());
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // Méthodes spécifiques pour les enseignants titulaires
    public function myClassStudents(Request $request)
    {
        $user = $request->user();

        if (!$user->class) {
            abort(403, 'Vous n\'êtes pas titulaire d\'une classe.');
        }

        $students = $user->class->students()->with('generalAverages')->get();

        return view('students.my-class', compact('students'));
    }

    public function myClassStudentShow(Student $student, Request $request)
    {
        $user = $request->user();

        if ($student->class_id !== $user->class_id) {
            abort(403, 'Cet élève ne fait pas partie de votre classe.');
        }

        $student->load(['marks.evaluation', 'averages.subject', 'generalAverages.term']);

        return view('students.my-class-show', compact('student'));
    }

    public function archives()
    {
        $this->authorize('view-students');

        $students = Student::onlyTrashed()
            ->with(['class', 'schoolYear'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(25);

        return view('students.archives', compact('students'));
    }

    public function restore($id)
    {
        $this->authorize('edit-students');

        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();

        return redirect()->route('admin.archives.students')
            ->with('success', 'Élève restauré avec succès.');
    }

    public function import(Request $request)
    {
        $this->authorize('create-students');

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048'
        ]);

        // Logique d'importation à implémenter
        return redirect()->route('admin.students.index')
            ->with('success', 'Importation des élèves en cours...');
    }

    public function exportTemplate()
    {
        $this->authorize('view-students');

        // Logique d'export du template à implémenter
        return response()->download(storage_path('templates/students_import_template.xlsx'));
    }


    public function showBulletinForm(Student $student)
{
    $this->authorize('generate-reports');
    
    // Vérifier que l'étudiant a une classe
    if (!$student->hasClass()) {
        return back()->with('error', "Cet élève n'est pas assigné à une classe. Veuillez d'abord assigner une classe à cet élève.");
    }

    $terms = Term::all();
    $schoolYears = SchoolYear::all();
    $currentTerm = Term::current()->first();
    $currentSchoolYear = SchoolYear::current();

    return view('students.bulletin-form', compact(
        'student',
        'terms',
        'schoolYears',
        'currentTerm',
        'currentSchoolYear'
    ));
}

    public function generateBulletinSimple(Student $student, Request $request)
    {
        $this->authorize('generate-reports');

        set_time_limit(180); // 3 minutes maximum

        try {
            Log::info("=== DÉBUT GÉNÉRATION BULLETIN SIMPLE ===");
            Log::info("Étudiant: {$student->id}, Nom: {$student->full_name}");

            // Vérifier que l'étudiant a une classe
            if (!$student->class_id) {
                Log::warning("Étudiant {$student->id} n'a pas de classe assignée");
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

            $term = \App\Models\Term::findOrFail($termId);
            $schoolYear = \App\Models\SchoolYear::findOrFail($schoolYearId);
            $schoolSettings = \App\Models\SchoolSetting::first();

            // Récupérer la classe
            $classe = \App\Models\Classe::find($student->class_id);
            if (!$classe) {
                return back()->with('error', "La classe de l'élève n'existe plus.");
            }

            Log::info("Paramètres: Classe={$classe->name}, Trimestre={$term->name}, Année={$schoolYear->year}");

            // FORCER le calcul des moyennes
            Log::info("Calcul des moyennes...");
            $calculationResult = $this->calculationService->calculateAllSubjectAverages($classe, $term, $schoolYear);
            
            if (!$calculationResult) {
                Log::error("Échec calcul moyennes pour étudiant {$student->id}");
                return back()->with('error', 'Impossible de calculer les moyennes. Vérifiez que les notes sont saisies.');
            }

            // Calculer les moyennes générales
            $this->calculationService->calculateGeneralAverages($classe, $term, $schoolYear);

            // Calculer les résultats
            Log::info("Calcul des résultats...");
            $results = $this->calculationService->calculateStudentResults(
                $student->id,
                $schoolYear->id,
                $term->id
            );

            Log::info("Résultats obtenus", [
                'moyenne' => $results['general_average'],
                'matieres' => count($results['subject_results'])
            ]);

            // Vérifier les résultats
            if (empty($results['subject_results'])) {
                Log::warning("Aucune matière trouvée pour l'étudiant {$student->id}");
                return back()->with('error', 'Aucune donnée académique disponible. Vérifiez les notes.');
            }

            // Calculer les statistiques de classe
            $classStats = $this->calculationService->calculateClassStatistics(
                $classe->id,
                $term->id,
                $schoolYear->id
            );

            // Enregistrer le bulletin
            $bulletin = \App\Models\Bulletin::updateOrCreate(
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
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $data)
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
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('ERREUR GÉNÉRATION BULLETIN SIMPLE: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }


}
