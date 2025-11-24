<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('matricule', 'password');

        // Tentative de connexion avec le matricule
        if (Auth::attempt(['matricule' => $credentials['matricule'], 'password' => $credentials['password'], 'is_active' => true])) {
            $request->session()->regenerate();

            // Mettre à jour la dernière connexion
            Auth::user()->update(['last_login_at' => now()]);

            // Redirection basée sur le rôle
            return $this->authenticated($request, Auth::user());
        }

        // Tentative alternative avec l'email
        if (Auth::attempt(['email' => $credentials['matricule'], 'password' => $credentials['password'], 'is_active' => true])) {
            $request->session()->regenerate();

            // Mettre à jour la dernière connexion
            Auth::user()->update(['last_login_at' => now()]);

            // Redirection basée sur le rôle
            return $this->authenticated($request, Auth::user());
        }

        throw ValidationException::withMessages([
            'matricule' => __('Les identifiants fournis sont incorrects ou votre compte est désactivé.'),
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        // Vérifier si le mot de passe est temporaire
        if ($user->isPasswordTemporary()) {
            return redirect()->route('password.change')
                ->with('warning', 'Pour des raisons de sécurité, veuillez changer votre mot de passe temporaire.');
        }

        // Redirection basée sur le rôle
        if ($user->isAdministrator()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isDirector()) {
            return redirect()->route('director.dashboard');
        } elseif ($user->isTitularTeacher()) {
            return redirect()->route('titular.dashboard');
        } elseif ($user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->isSecretary()) {
            return redirect()->route('secretary.dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        // Journaliser la déconnexion
        if (Auth::check()) {
            // activity()
            //     ->causedBy(Auth::user())
            //     ->log('Déconnexion de l\'application');
            // \Log::info('User logged out', ['user_id' => auth()->id()]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
