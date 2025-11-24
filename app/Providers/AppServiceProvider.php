<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // Services
        $this->app->singleton(\App\Services\MarkCalculationService::class, function ($app) {
            return new \App\Services\MarkCalculationService();
        });

        // Configuration des routes
        $this->configureRoutes();
    }

    protected function configureRoutes(): void
    {
        // Route pattern constraints
        Route::pattern('id', '[0-9]+');
        Route::pattern('student', '[0-9]+');
        Route::pattern('teacher', '[0-9]+');
        Route::pattern('classe', '[0-9]+');
        Route::pattern('subject', '[0-9]+');
        Route::pattern('evaluation', '[0-9]+');
        Route::pattern('mark', '[0-9]+');
        Route::pattern('audit', '[0-9]+');
        Route::pattern('notification', '[0-9]+');
        Route::pattern('term', '[0-9]+');
        Route::pattern('schoolYear', '[0-9]+');
    }
}
