<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur connecté
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $user->load(['roles', 'teacherAssignments.classe', 'teacherAssignments.subject']);

        // Statistiques pour le tableau de bord du profil
        $stats = $this->getUserStats($user);

        return view('profile.show', compact('user', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition du profil
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Mettre à jour le profil
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Gérer l'upload de l'avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar s'il existe
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        $user->update($validated);

        // Notifier l'utilisateur
        NotificationService::notifyUser(
            $user,
            'Profil mis à jour',
            'Vos informations de profil ont été mises à jour avec succès.',
            'system',
            [],
            route('profile.show')
        );

        return redirect()->route('profile.show')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function editPassword(Request $request)
    {
        return view('profile.password');
    }

    /**
     * Changer le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Notifier l'utilisateur du changement de mot de passe
        NotificationService::notifyUser(
            $user,
            'Mot de passe modifié',
            'Votre mot de passe a été modifié avec succès.',
            'security',
            [],
            route('profile.show')
        );

        return redirect()->route('profile.show')
            ->with('success', 'Mot de passe modifié avec succès.');
    }

    /**
     * Afficher l'activité de l'utilisateur
     */
    public function activity(Request $request)
    {
        $user = $request->user();
        $activities = $user->audits()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.activity', compact('user', 'activities'));
    }

    /**
     * Afficher les notifications de l'utilisateur
     */
    public function notifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.notifications', compact('user', 'notifications'));
    }

    /**
     * Obtenir les statistiques de l'utilisateur
     */
    private function getUserStats(User $user)
    {
        $stats = [];

        if ($user->hasRole('enseignant') || $user->hasRole('enseignant titulaire')) {
            $stats = array_merge($stats, $this->getTeacherStats($user));
        }

        if ($user->hasRole('administrateur')) {
            $stats = array_merge($stats, $this->getAdminStats($user));
        }

        if ($user->hasRole('directeur')) {
            $stats = array_merge($stats, $this->getDirectorStats($user));
        }

        if ($user->hasRole('secretaire')) {
            $stats = array_merge($stats, $this->getSecretaryStats($user));
        }

        return $stats;
    }

    /**
     * Statistiques pour les enseignants
     */
    private function getTeacherStats(User $user)
    {
        $currentYear = \App\Models\SchoolYear::current();
        $currentTerm = \App\Models\Term::current();

        return [
            'classes_count' => $user->teacherAssignments()->where('school_year_id', $currentYear->id)->count(),
            'subjects_count' => $user->teacherAssignments()->where('school_year_id', $currentYear->id)->distinct('subject_id')->count('subject_id'),
            // 'evaluations_count' => $user->evaluations()->where('school_year_id', $currentYear->id)->count(),
            // 'pending_marks_count' => $user->evaluations()
            //     ->where('school_year_id', $currentYear->id)
            //     ->where('term_id', $currentTerm->id)
            //     ->whereDoesntHave('marks')
            //     ->count(),
        ];
    }

    /**
     * Statistiques pour l'administrateur
     */
    private function getAdminStats(User $user)
    {
        return [
            'users_count' => User::count(),
            'active_users_count' => User::where('is_active', true)->count(),
            'classes_count' => \App\Models\Classe::count(),
            'students_count' => \App\Models\Student::count(),
        ];
    }

    /**
     * Statistiques pour le directeur
     */
    private function getDirectorStats(User $user)
    {
        $currentYear = \App\Models\SchoolYear::current();
        $currentTerm = \App\Models\Term::current();

        return [
            'teachers_count' => User::role(['enseignant', 'enseignant titulaire'])->count(),
            'classes_count' => \App\Models\Classe::where('school_year_id', $currentYear->id)->count(),
            'students_count' => \App\Models\Student::where('school_year_id', $currentYear->id)->count(),
            'average_school' => \App\Models\GeneralAverage::where('school_year_id', $currentYear->id)
                ->where('term_id', $currentTerm->id)
                ->avg('average') ?? 0,
        ];
    }

    /**
     * Statistiques pour le secrétaire
     */
    private function getSecretaryStats(User $user)
    {
        $currentYear = \App\Models\SchoolYear::current();

        return [
            'students_count' => \App\Models\Student::where('school_year_id', $currentYear->id)->count(),
            'bulletins_generated' => \App\Models\Bulletin::where('school_year_id', $currentYear->id)->count(),
            'new_students' => \App\Models\Student::where('school_year_id', $currentYear->id)
                ->where('created_at', '>=', now()->subMonth())
                ->count(),
        ];
    }
}