<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\SchoolYear;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use App\Services\MarkCalculationService;

class ClasseController extends Controller
{
    protected $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->middleware('auth');
        $this->calculationService = $calculationService;
    }

    public function index(Request $request)
    {
        $this->authorize('view-classes');

        $query = Classe::with(['teacher', 'schoolYear', 'students']);

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $classes = $query->orderBy('level')->orderBy('name')->paginate(20);
        $currentSchoolYear = SchoolYear::current();

        return view('classes.index', compact('classes', 'currentSchoolYear'));
    }

    public function create()
    {
        $this->authorize('create-classes');

        $teachers = \App\Models\User::role(['enseignant titulaire'])->active()->get();
        $schoolYears = SchoolYear::all();
        $levels = ['6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Tle'];

        return view('classes.create', compact('teachers', 'schoolYears', 'levels'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-classes');

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'level' => 'required|string|max:20',
            'section' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:60',
            'head_teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'required|exists:school_years,id',
        ]);

        Classe::create([
            'name' => $validated['name'],
            'level' => $validated['level'],
            'section' => $validated['section'] ?? null,
            'capacity' => $validated['capacity'],
            'teacher_id' => $validated['head_teacher_id'] ?? null,
            'school_year_id' => $validated['academic_year'],
        ]);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe créée avec succès.');
    }

    public function show(Classe $classe, Request $request)
    {
        $this->authorize('view-classes');

        $currentTerm = \App\Models\Term::current();
        $currentSchoolYear = SchoolYear::current();

        $classe = $classe->load(['teacher', 'students', 'teacherAssignments.teacher', 'teacherAssignments.subject']);

        // Statistiques de la classe
         if ($classe) {
        $classStats = $this->calculationService->calculateClassStatistics($classe, $currentTerm, $currentSchoolYear);
    } else {
        $classStats = [];
    }
        // $classStats = $this->calculationService->calculateClassStatistics($classe->id, $currentTerm->id, $currentSchoolYear->id);

        return view('classes.show', compact('classe', 'classStats', 'currentTerm', 'currentSchoolYear'));
    }

    public function edit(Classe $class)
    {
        $this->authorize('edit-classes');

        $teachers = \App\Models\User::role(['enseignant titulaire'])->active()->get();
        $schoolYears = SchoolYear::all();
        $levels = ['6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Tle'];

        return view('classes.edit', compact('class', 'teachers', 'schoolYears', 'levels'));
    }

    public function update(Request $request, Classe $class)
    {
        $this->authorize('edit-classes');

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'level' => 'required|string|max:20',
            'section' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:60',
            'teacher_id' => 'nullable|exists:users,id',
            'school_year_id' => 'required|exists:school_years,id',
            'is_active' => 'boolean',
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe mise à jour avec succès.');
    }

    public function destroy(Classe $classe)
    {
        $this->authorize('delete-classes');

        // Vérifier s'il y a des élèves dans la classe
        if ($classe->students()->count() > 0) {
            return redirect()->route('classes.index')
                ->with('error', 'Impossible de supprimer une classe contenant des élèves.');
        }

        $classe->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe supprimée avec succès.');
    }

    public function myClass(Request $request)
    {
        $user = $request->user();

        if (!$user->class) {
            abort(403, 'Vous n\'êtes pas titulaire d\'une classe.');
        }

        $classe = $user->class;
        $currentTerm = \App\Models\Term::current();
        $currentSchoolYear = SchoolYear::current();

        $classe->load(['students', 'teacherAssignments.teacher', 'teacherAssignments.subject']);

        $classStats = $this->calculationService->calculateClassStatistics($classe, $currentTerm, $currentSchoolYear);

        return view('classes.my-class', compact('classe', 'classStats', 'currentTerm', 'currentSchoolYear'));
    }

    public function getStatistics(Classe $classe, Request $request)
    {
        $termId = $request->get('term_id');
        $schoolYearId = $request->get('school_year_id');

        $term = $termId ? \App\Models\Term::find($termId) : \App\Models\Term::current();
        $schoolYear = $schoolYearId ? SchoolYear::find($schoolYearId) : SchoolYear::current();

        $statistics = $this->calculationService->calculateClassStatistics($classe, $term, $schoolYear);

        return response()->json($statistics);
    }

    public function assignSubjects(Classe $classe, Request $request)
    {
        $this->authorize('edit-classes');

        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.teacher_id' => 'required|exists:users,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
        ]);

        $currentSchoolYear = SchoolYear::current();

        // Supprimer les anciennes affectations
        TeacherAssignment::where('class_id', $classe->id)
            ->where('school_year_id', $currentSchoolYear->id)
            ->delete();

        // Créer les nouvelles affectations
        foreach ($validated['assignments'] as $assignment) {
            TeacherAssignment::create([
                'teacher_id' => $assignment['teacher_id'],
                'class_id' => $classe->id,
                'subject_id' => $assignment['subject_id'],
                'school_year_id' => $currentSchoolYear->id,
                'is_titular' => false,
            ]);
        }

        return redirect()->route('admin.classes.show', $classe)
            ->with('success', 'Affectations des matières mises à jour avec succès.');
    }

    public function assignTeacher(Classe $classe, Request $request)
    {
        $this->authorize('edit-classes');

        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id'
        ]);

        $classe->update(['teacher_id' => $validated['teacher_id']]);


        return redirect()->route('admin.classes.show', $classe)
            ->with('success', 'Enseignant titulaire assigné avec succès.');
    }

    public function myClasses(Request $request)
    {
        $user = $request->user();

        $assignedClasses = $user->assignedClasses()->with(['students', 'teacher.user'])->get();

        return view('classes.my-classes', compact('assignedClasses'));
    }
}
