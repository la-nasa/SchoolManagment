<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view-subjects');

        $query = Subject::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Tri
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        if (in_array($sort, ['name', 'code', 'coefficient', 'created_at'])) {
            $query->orderBy($sort, $direction);
        }

        // CORRECTION : Utiliser la relation teachers avec withCount
        $subjects = $query->withCount('teachers')->paginate(25);

        // Calcul des statistiques pour la vue
        $activeSubjectsCount = Subject::where('is_active', true)->count();
        $averageCoefficient = Subject::avg('coefficient') ?? 0;

        return view('subjects.index', compact(
            'subjects',
            'activeSubjectsCount',
            'averageCoefficient'
        ));
    }

    public function create()
    {
        $this->authorize('create-subjects');

        return view('subjects.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create-subjects');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects',
            'code' => 'required|string|max:10|unique:subjects',
            'coefficient' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Matière créée avec succès.');
    }

    public function show(Subject $subject)
    {
        $this->authorize('view-subjects');

        $subject->load(['teachers', 'teacherAssignments.teacher', 'teacherAssignments.class', 'evaluations.class']);

        // Compter les relations
        $subject->teachers_count = $subject->teachers->count();
        $subject->classes_count = $subject->classes->count();
        $subject->evaluations_count = $subject->evaluations->count();



        return view('subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        $this->authorize('edit-subjects');

        return view('subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorize('edit-subjects');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,' . $subject->id,
            'code' => 'required|string|max:10|unique:subjects,code,' . $subject->id,
            'coefficient' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Matière mise à jour avec succès.');
    }

    public function destroy(Subject $subject)
    {
        $this->authorize('delete-subjects');

        // Vérifier s'il y a des évaluations associées
        if ($subject->evaluations()->count() > 0) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'Impossible de supprimer une matière associée à des évaluations.');
        }

        // Vérifier s'il y a des affectations d'enseignants
        if ($subject->teacherAssignments()->count() > 0) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'Impossible de supprimer une matière associée à des enseignants.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Matière supprimée avec succès.');
    }
}
