<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classe;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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
        $student->load(['class.teacher', 'schoolYear', 'marks.evaluation.subject', 'averages.subject', 'generalAverages.term']);

        return view('students.show', compact('student'));
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

}
