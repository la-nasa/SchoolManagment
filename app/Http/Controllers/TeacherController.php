<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class TeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view-users');

        $query = User::role(['enseignant', 'enseignant titulaire'])->with(['roles', 'class']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('name')->paginate(25);

        return view('teachers.index', compact('teachers'));
    }

    public function create()
    {
        $this->authorize('create-users');

        $classes = Classe::active()->get();
        $subjects = Subject::active()->get();
        $roles = ['enseignant', 'enseignant titulaire'];

        return view('teachers.create', compact('classes', 'subjects', 'roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:M,F',
            'role' => 'required|in:enseignant,enseignant titulaire',
            'class_id' => 'nullable|required_if:role,enseignant titulaire|exists:classes,id',
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Générer le matricule et mot de passe temporaire
        $temporaryPassword = User::generateTemporaryPassword();

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($temporaryPassword),
            'matricule' => User::generateMatricule(),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'class_id' => $validated['class_id'] ?? null,
            'is_active' => true,
        ];

        if ($request->hasFile('photo')) {
            $userData['photo'] = $request->file('photo')->store('teachers/photos', 'public');
        }

        $teacher = User::create($userData);
        $teacher->assignRole($validated['role']);

        // Créer les affectations
        $currentSchoolYear = \App\Models\SchoolYear::current();
        foreach ($validated['subjects'] as $subjectId) {
            TeacherAssignment::create([
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectId,
                'class_id' => $validated['class_id'],
                'school_year_id' => $currentSchoolYear->id,
                'is_titular' => $validated['role'] === 'enseignant titulaire',
            ]);
        }

        return redirect()->route('teachers.index')
            ->with('success', "Enseignant créé avec succès. Matricule: {$teacher->matricule}, Mot de passe temporaire: {$temporaryPassword}");
    }

    public function show(User $user)
    {
        $this->authorize('view-users');

         $teacher= $user->load([
            'roles',
            'class',
            'teacherAssignments.subject',
            'teacherAssignments.class',
            'assignedClasses',
            'assignedSubjects'
        ]);
        // $teacher->load(['roles', 'class', 'teacherAssignments.subject', 'teacherAssignments.class']);
        // $teacher = Teacher::with([
        //     'class',
        //     'teacherAssignments.class',
        //     'teacherAssignments.subject'
        // ])->where('user_id', $user->id)->firstOrFail();


        return view('teachers.show', compact('teacher'));
    }

    public function edit(User $user)
    {
        $this->authorize('edit-users');

        $classes = Classe::active()->get();
        $subjects = Subject::active()->get();
        $roles = ['enseignant', 'enseignant titulaire'];
        $teacher = $user->load('teacherAssignments', 'roles', 'class');
        // $teacher->load('teacherAssignments');

        return view('teachers.edit', compact('teacher', 'classes', 'subjects', 'roles'));
    }

    public function update(Request $request, User $teacher)
    {
        $this->authorize('edit-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $teacher->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:M,F',
            'role' => 'required|in:enseignant,enseignant titulaire',
            'class_id' => 'nullable|required_if:role,enseignant titulaire|exists:classes,id',
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'class_id' => $validated['class_id'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('photo')) {
            if ($teacher->photo) {
                \Storage::disk('public')->delete($teacher->photo);
            }
            $userData['photo'] = $request->file('photo')->store('teachers/photos', 'public');
        }

        $teacher->update($userData);

        // Mettre à jour le rôle
        $teacher->syncRoles([$validated['role']]);

        // Mettre à jour les affectations
        $currentSchoolYear = \App\Models\SchoolYear::current();
        TeacherAssignment::where('teacher_id', $teacher->id)->delete();

          if ($currentSchoolYear) {
            foreach ($validated['subjects'] as $subjectId) {
                TeacherAssignment::create([
                    'teacher_id' => $user->id,
                    'subject_id' => $subjectId,
                    'class_id' => $validated['class_id'] ?? null,
                    'school_year_id' => $currentSchoolYear->id,
                    'is_titular' => $validated['role'] === 'enseignant titulaire',
                ]);
            }
        }
        // foreach ($validated['subjects'] as $subjectId) {
        //     TeacherAssignment::create([
        //         'teacher_id' => $teacher->id,
        //         'subject_id' => $subjectId,
        //         'class_id' => $validated['class_id'],
        //         'school_year_id' => $currentSchoolYear->id,
        //         'is_titular' => $validated['role'] === 'enseignant titulaire',
        //     ]);
        // }


        return redirect()->route('teachers.index')
            ->with('success', 'Enseignant mis à jour avec succès.');
    }

    public function destroy(User $teacher)
    {
        $this->authorize('delete-users');

        $teacher->update(['is_active' => false]);

        return redirect()->route('teachers.index')
            ->with('success', 'Enseignant désactivé avec succès.');
    }

    public function profile()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                \Storage::disk('public')->delete($user->photo);
            }
            $validated['photo'] = $request->file('photo')->store('teachers/photos', 'public');
        }

        $user->update($validated);

        return redirect()->route('profile')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    public function resetPassword(User $user)
    {
        $this->authorize('edit-users');

        $temporaryPassword = User::generateTemporaryPassword();
        $teacher= $user->update([
            'password' => Hash::make($temporaryPassword),
            'password_changed_at' => null,
        ]);

        return redirect()->route('admin.users.show', $teacher)
            ->with('success', "Mot de passe réinitialisé. Nouveau mot de passe temporaire: {$temporaryPassword}");
    }

    public function activate(User $user)
    {
        $this->authorize('edit-users');

        $user->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Enseignant activé avec succès.');
    }

    public function deactivate(User $user)
    {
        $this->authorize('edit-users');

        $user->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'Enseignant désactivé avec succès.');
    }

}
