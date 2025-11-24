<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class PasswordChangeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showChangeForm()
    {
        return view('auth.change-password');
    }

    public function change(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Le mot de passe actuel est incorrect.');
                }
            }],
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'new_password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
        ]);

        // Journaliser le changement de mot de passe
        // activity()
        //     ->causedBy($user)
        //     ->log('Changement de mot de passe');
        \Log::info('Changement de Mot de passe', ['user_id' => auth()->id()]);

        // Redirection basée sur le rôle
        if ($user->isAdministrator()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Votre mot de passe a été changé avec succès.');
        } elseif ($user->isDirector()) {
            return redirect()->route('director.dashboard')
                ->with('success', 'Votre mot de passe a été changé avec succès.');
        } elseif ($user->isTitularTeacher()) {
            return redirect()->route('titular.dashboard')
                ->with('success', 'Votre mot de passe a été changé avec succès.');
        } elseif ($user->isTeacher()) {
            return redirect()->route('teacher.dashboard')
                ->with('success', 'Votre mot de passe a été changé avec succès.');
        } elseif ($user->isSecretary()) {
            return redirect()->route('secretary.dashboard')
                ->with('success', 'Votre mot de passe a été changé avec succès.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Votre mot de passe a été changé avec succès.');
    }
}
