<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes API authentifiées
Route::middleware(['auth:sanctum'])->group(function () {

    // Dashboard et statistiques
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/quick-stats', [DashboardController::class, 'getQuickStats']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
        Route::get('/recent', [NotificationController::class, 'getRecentNotifications']);
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // Données des classes
    Route::prefix('classes')->group(function () {
        Route::get('/{classe}/statistics', [ClasseController::class, 'getStatistics']);
        Route::get('/{classe}/students', [ClasseController::class, 'getStudents']);
        Route::get('/{classe}/performance', [ClasseController::class, 'getPerformanceData']);
    });

    // Données des élèves
    Route::prefix('students')->group(function () {
        Route::get('/{student}/progress', [StudentController::class, 'getProgress']);
        Route::get('/{student}/marks', [StudentController::class, 'getMarks']);
        Route::get('/{student}/averages', [StudentController::class, 'getAverages']);
    });

    // Recherche
    Route::prefix('search')->group(function () {
        Route::get('/students', [StudentController::class, 'search']);
        Route::get('/teachers', [TeacherController::class, 'search']);
        Route::get('/classes', [ClasseController::class, 'search']);
    });

    // Données pour les graphiques
    Route::prefix('charts')->group(function () {
        Route::get('/class-performance/{classe}', [ClasseController::class, 'getChartData']);
        Route::get('/school-performance', [DashboardController::class, 'getSchoolChartData']);
        Route::get('/teacher-performance', [TeacherController::class, 'getPerformanceChartData']);
    });

    // Export de données
    Route::prefix('export')->group(function () {
        Route::get('/class-marks/{classe}', [MarkController::class, 'exportClassMarks']);
        Route::get('/student-bulletin/{student}', [ReportController::class, 'exportBulletin']);
        Route::get('/evaluation-pv/{evaluation}', [ReportController::class, 'exportPV']);
    });
});
