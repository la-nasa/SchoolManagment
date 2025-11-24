<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTemporaryPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isPasswordTemporary() && !$request->routeIs('password.change')) {
            return redirect()->route('password.change')
                ->with('warning', 'Veuillez changer votre mot de passe temporaire avant de continuer.');
        }

        return $next($request);
    }
}
