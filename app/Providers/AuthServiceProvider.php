<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Gates pour l'audit
        Gate::define('view-audit-trail', function ($user) {
            return $user->isAdministrator() ||
                   ($user->isDirector() && $user->can('view-audit-trail'));
        });

        Gate::define('export-audit-trail', function ($user) {
            return $user->isAdministrator();
        });

        // Gates pour la gestion des notes
        Gate::define('manage-marks', function ($user, $evaluation) {
            if ($user->isAdministrator() || $user->isDirector()) {
                return true;
            }

            if ($user->isTeacher() || $user->isTitularTeacher()) {
                return $user->teacherAssignments()
                    ->where('class_id', $evaluation->class_id)
                    ->where('subject_id', $evaluation->subject_id)
                    ->exists();
            }

            return false;
        });
    }
}
