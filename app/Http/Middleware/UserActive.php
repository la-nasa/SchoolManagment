<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté et actif
        if (Auth::check() && Auth::user()->active) {
            return $next($request);
        }

        // Rediriger ou renvoyer une erreur si l'utilisateur n'est pas actif
        return redirect('/')->with('error', 'Votre compte n\'est pas actif.');
        
        // Ou pour les API :
        // return response()->json(['error' => 'Compte non actif'], 403);
    }
}