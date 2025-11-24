<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{


    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté et actif
        if (Auth::check()) {
            $user = Auth::user();

            if(!$user->is_active){
                Auth::logout();
                return redirect('/login')->with('error', 'votre compte est desactive.');
            }
        }

        return $next($request);

    }
}
