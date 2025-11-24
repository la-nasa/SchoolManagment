<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Evaluation;
use App\Models\TeacherAssignment;

class CheckTeacherPermissions
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || (!$user->isTeacher() && !$user->isTitularTeacher())) {
            abort(403, 'Accès réservé aux enseignants.');
        }

        // Vérification pour l'accès aux évaluations
        if ($request->route('evaluation')) {
            $evaluation = $request->route('evaluation');
            if (!$evaluation instanceof Evaluation) {
                $evaluation = Evaluation::findOrFail($evaluation);
            }

            $isAssigned = TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $evaluation->class_id)
                ->where('subject_id', $evaluation->subject_id)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette classe/matière.');
            }
        }

        // Vérification pour l'accès aux classes
        if ($request->route('classe')) {
            $classId = $request->route('classe');
            $isAssigned = TeacherAssignment::where('teacher_id', $user->id)
                ->where('class_id', $classId)
                ->exists();

            if (!$isAssigned) {
                abort(403, 'Vous n\'êtes pas assigné à cette classe.');
            }
        }

        return $next($request);
    }
}
