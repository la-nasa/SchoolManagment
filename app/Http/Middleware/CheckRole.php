<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if(empty($roles)){
            return $next($request);
        }
        // Vérifier si l'utilisateur a un des rôles requis
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Vérifier les permissions spécifiques pour l'audit
        if (in_array('audit-access', $roles) && $user->canAccessAudit()) {
            return $next($request);
        }

        abort(403, 'Accès non autorisé pour votre rôle.');
    }
}
