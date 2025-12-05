<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AcademicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BulletinController;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\CheckRole;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page d'accueil publique
Route::get('/', function () {
    return view('auth.login');
})->name('home');

// Authentification
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes authentifiées
Route::middleware(['auth', CheckUserActive::class])->group(function () {

    // Tableau de bord général
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Changement de mot de passe (accessible à tous les utilisateurs authentifiés)
    Route::get('/password/change', [PasswordChangeController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'change']);

    // Profil utilisateur
    Route::get('/profile', [TeacherController::class, 'profile'])->name('profile');
    Route::put('/profile', [TeacherController::class, 'updateProfile'])->name('profile.update');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    // Routes pour les paramètres de notifications
    Route::get('/notifications/settings', [NotificationController::class, 'settings'])->name('notifications.settings');
    Route::put('/notifications/settings', [NotificationController::class, 'updateSettings'])->name('notifications.settings.update');

    // Routes pour l'administrateur
    Route::middleware([CheckRole::class . ':administrateur'])->prefix('admin')->name('admin.')->group(function () {
        // Tableau de bord administrateur
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // Gestion des utilisateurs
        Route::resource('users', TeacherController::class)->except(['show']);
        Route::get('/users/{user}', [TeacherController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/reset-password', [TeacherController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/activate', [TeacherController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/deactivate', [TeacherController::class, 'deactivate'])->name('users.deactivate');

    // Route::prefix('teachers')->name('teachers.')->group(function () {
    //     Route::get('/', [TeacherController::class, 'index'])->name('index');
    //     Route::get('/create', [TeacherController::class, 'create'])->name('create');
    //     Route::post('/', [TeacherController::class, 'store'])->name('store');
    //     Route::get('/{user}', [TeacherController::class, 'show'])->name('show');
    //     Route::get('/{user}/edit', [TeacherController::class, 'edit'])->name('edit');
    //     Route::put('/{user}', [TeacherController::class, 'update'])->name('update');
    //     Route::delete('/{user}', [TeacherController::class, 'destroy'])->name('destroy');
    //     Route::post('/{user}/reset-password', [TeacherController::class, 'resetPassword'])->name('reset-password');
    // });
         Route::resource('students', StudentController::class);

    // Routes supplémentaires pour les étudiants
    Route::get('/students/{student}/bulletin', [ReportController::class, 'generateBulletin'])->name('students.bulletin');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('/students/export/template', [StudentController::class, 'exportTemplate'])->name('students.export-template');

    Route::post('/students/{student}/generate-bulletin-simple', [StudentController::class, 'generateBulletinSimple'])
            ->name('students.generate-bulletin-simple');
        
    Route::get('/students/{student}/bulletin-form', [StudentController::class, 'showBulletinForm'])->name('students.bulletin-form');
    Route::post('/students/{student}/generate-bulletin', [StudentController::class, 'generateBulletin'])->name('students.generate-bulletin');
    // Archives et restauration
    Route::get('/archives/students', [StudentController::class, 'archives'])->name('archives.students');
    Route::post('/archives/students/{id}/restore', [StudentController::class, 'restore'])->name('archives.students.restore');
    Route::post('/{student}/generate-bulletin', [StudentController::class, 'generateBulletin'])
        ->name('generate-bulletin');
        
    // Route simple pour générer bulletin
    Route::get('/{student}/generate-bulletin-simple', [StudentController::class, 'generateBulletinSimple'])
        ->name('generate-bulletin-simple');
        // Gestion des classes
        Route::resource('classes', ClasseController::class)->except(['show']);
        Route::get('/classes/{classe}', [ClasseController::class, 'show'])->name('classes.show');
        Route::post('/classes/{classe}/assign-subjects', [ClasseController::class, 'assignSubjects'])->name('classes.assign-subjects');
        Route::post('/classes/{classe}/assign-teacher', [ClasseController::class, 'assignTeacher'])->name('classes.assign-teacher');
        Route::post('classes', [ClasseController::class, 'store'])->name('classes.store');
        Route::get('classes/create', [ClasseController::class, 'create'])->name('classes.create');
        Route::get('/classes/{classe}/statistics', [ClasseController::class, 'getStatistics'])->name('classes.statistics');

        // Gestion des matières
        Route::resource('subjects', SubjectController::class);

        // Gestion des évaluations
        Route::resource('evaluations', EvaluationController::class);
        Route::get('/evaluations/{evaluation}/marks', [EvaluationController::class, 'showMarks'])->name('evaluations.marks');
        Route::post('evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');

        // Gestion des notes
        // Route::resource('marks', MarkController::class)->only(['create', 'edit', 'update', 'store', 'destroy']);
        // Route::post('/evaluations/{evaluation}/marks/bulk-update', [MarkController::class, 'bulkUpdate'])->name('marks.bulk-update');
        Route::resource('marks', MarkController::class)->only(['edit', 'update', 'destroy']);
        Route::get('/evaluations/{evaluation}/marks', [MarkController::class, 'create'])->name('marks.create');
        Route::post('/evaluations/{evaluation}/marks', [MarkController::class, 'store'])->name('marks.store');

        // Audit
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/{audit}', [AuditController::class, 'show'])->name('audit.show');
        Route::get('/audit/user/{user}', [AuditController::class, 'userActivity'])->name('audit.user');
        Route::get('/audit/export', [AuditController::class, 'export'])->name('audit.export');

        // Bulletins et Rapports
        Route::prefix('bulletins')->name('bulletins.')->group(function () {
            Route::post('/classes/{classe}/generate', [BulletinController::class, 'generateClassBulletins'])->name('generate-class');
            Route::post('/classes/{classe}/pv', [BulletinController::class, 'generateClassPV'])->name('generate-pv');
            Route::get('/{bulletin}', [BulletinController::class, 'show'])->name('show');
            Route::get('/{bulletin}/pdf', [BulletinController::class, 'downloadPDF'])->name('download-pdf');
            Route::post('/{bulletin}/archive', [BulletinController::class, 'archive'])->name('archive');
            Route::get('/archived/list', [BulletinController::class, 'archived'])->name('archived');
            Route::get('/student/{student}/generate', [BulletinController::class, 'generateStudentBulletinSimple'])
                ->name('generate-student-simple');
            Route::post('/student/{student}/generate', [BulletinController::class, 'generateStudentBulletin'])
                ->name('generate-student');
            
            Route::post('/students/{student}/generate-bulletin', [StudentController::class, 'generateBulletin'])
                ->name('generate-bulletin');
        });

        

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/school', [ReportController::class, 'schoolReport'])->name('school');
            Route::get('/teachers', [ReportController::class, 'teachersReport'])->name('teachers');
            Route::get('/performance', [ReportController::class, 'performanceReport'])->name('performance');
            Route::get('/class/{classe}', [ReportController::class, 'classReport'])->name('class');
            Route::get('/archived-bulletins', [ReportController::class, 'archivedBulletins'])->name('archived-bulletins');
            Route::get('/bulletin/{student}', [ReportController::class, 'generateBulletin'])->name('bulletin');
            Route::get('/pv/{evaluation}', [ReportController::class, 'generatePV'])->name('pv');
            Route::get('/classes/{classe}/generate-pv', [BulletinController::class, 'generateClassReport'])
                ->name('classes.generate-pv');

        });
        // Rapports administrateur
    //     Route::prefix('reports')->name('reports.')->group(function () {
    //         Route::get('/school', [ReportController::class, 'schoolReport'])->name('school');
    //         Route::get('/teachers', [ReportController::class, 'teachersReport'])->name('teachers');
    //         Route::get('/performance', [ReportController::class, 'performanceReport'])->name('performance');
    //         Route::get('/class/{classe}', [ReportController::class, 'classReport'])->name('class');
    //         Route::get('/archived-bulletins', [ReportController::class, 'archivedBulletins'])->name('archived-bulletins');

    //         // Génération de documents
    //         Route::get('/bulletin/{student}', [ReportController::class, 'generateBulletin'])->name('bulletin');
    //         Route::get('/class-bulletins/{classe}', [ReportController::class, 'generateClassBulletins'])->name('class-bulletins');
    //         Route::get('/pv/{evaluation}', [ReportController::class, 'generatePV'])->name('pv');
    //         Route::post('classes/{classe}/generate', [BulletinController::class, 'generateClassBulletins'])
    //     ->name('generate-class');
    // Route::post('classes/{classe}/pv', [BulletinController::class, 'generateClassPV'])
    //     ->name('generate-pv');
    // Route::get('{bulletin}', [BulletinController::class, 'show'])->name('show');
    // Route::get('{bulletin}/pdf', [BulletinController::class, 'downloadPDF'])->name('download-pdf');
    // Route::post('{bulletin}/archive', [BulletinController::class, 'archive'])->name('archive');
    // Route::get('archived/list', [BulletinController::class, 'archived'])->name('archived');
    //     });

        // Paramètres
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::put('/settings/appearance', [SettingController::class, 'updateAppearance'])->name('settings.appearance');
        Route::put('/settings/academic', [SettingController::class, 'updateAcademic'])->name('settings.academic');
        Route::put('settings/update', [SettingController::class, 'update'])->name('settings.update');
        Route::put('settings/updatesecutity', [SettingController::class, 'updatesecurity'])->name('settings.updatesecurity');


        // Gestion académique
        Route::prefix('academic')->name('academic.')->group(function () {
            Route::get('/school-years', [AcademicController::class, 'schoolYears'])->name('school-years');
            Route::get('/dashboard', [AcademicController::class, 'dashboard'])->name('dashboard');
            Route::get('/school-years/create', [AcademicController::class, 'createSchoolYear'])->name('school-years.create');
            Route::post('/school-years', [AcademicController::class, 'storeSchoolYear'])->name('school-years.store');
            Route::get('/school-years/{schoolYear}/edit', [AcademicController::class, 'editSchoolYear'])->name('school-years.edit');
            Route::put('/school-years/{schoolYear}', [AcademicController::class, 'updateSchoolYear'])->name('school-years.update');
            Route::delete('/school-years/{schoolYear}', [AcademicController::class, 'destroySchoolYear'])->name('school-years.destroy');

            Route::get('/school-years/{schoolYear}/terms', [AcademicController::class, 'terms'])->name('terms');
            Route::get('/school-years/{schoolYear}/terms/create', [AcademicController::class, 'createTerm'])->name('terms.create');
            Route::post('/school-years/{schoolYear}/terms', [AcademicController::class, 'storeTerm'])->name('terms.store');
            Route::get('/terms/{term}/edit', [AcademicController::class, 'editTerm'])->name('terms.edit');
            Route::put('/terms/{term}', [AcademicController::class, 'updateTerm'])->name('terms.update');
            Route::delete('/terms/{term}', [AcademicController::class, 'destroyTerm'])->name('terms.destroy');
            Route::post('terms/{term}/archive', [AcademicController::class, 'archiveTerm'])->name('terms.archive');
            Route::post('terms/{term}/restore', [AcademicController::class, 'restoreTerm'])->name('terms.restore');
        });
    });

    // Routes pour le directeur
    Route::middleware([CheckRole::class . ':directeur'])->prefix('director')->name('director.')->group(function () {
        // Tableau de bord directeur
        Route::get('/dashboard', [DashboardController::class, 'directordashboard'])->name('dashboard');

        // Consultation des données
        Route::get('/classes', [ClasseController::class, 'index'])->name('classes');
        Route::get('/classes/{classe}', [ClasseController::class, 'show'])->name('classes.show');
        Route::get('/classes/{classe}/statistics', [ClasseController::class, 'getStatistics'])->name('classes.statistics');

        Route::get('/students', [StudentController::class, 'index'])->name('students');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');

        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers');
        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');

        Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations');
        Route::get('/evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');

        // Rapports directeur
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/performance', [ReportController::class, 'performanceReport'])->name('performance');
            Route::get('/class/{classe}', [ReportController::class, 'classReport'])->name('class');
            Route::get('/school', [ReportController::class, 'schoolReport'])->name('school');
            Route::get('/teachers', [ReportController::class, 'teachersReport'])->name('teachers');

            // Génération de documents
            Route::get('/bulletin/{student}', [ReportController::class, 'generateBulletin'])->name('bulletin');
            Route::get('/class-bulletins/{classe}', [ReportController::class, 'generateClassBulletins'])->name('class-bulletins');
            Route::get('/pv/{evaluation}', [ReportController::class, 'generatePV'])->name('pv');
        });

        // Audit (si autorisé)
        Route::middleware(['can:view-audit-trail'])->group(function () {
            Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
            Route::get('/audit/{audit}', [AuditController::class, 'show'])->name('audit.show');
        });
    });

    // Routes pour l'enseignant titulaire
    Route::middleware([CheckRole::class . ':enseignant titulaire'])->prefix('titular')->name('titular.')->group(function () {
        // Tableau de bord titulaire
        Route::get('/dashboard', [DashboardController::class, 'titulardashboard'])->name('dashboard');

        // Gestion de la classe
        Route::get('/my-class', [ClasseController::class, 'myClass'])->name('my-class');
        Route::get('/my-class/students', [StudentController::class, 'myClassStudents'])->name('students');
        Route::get('/my-class/students/{student}', [StudentController::class, 'myClassStudentShow'])->name('students.show');

        // Évaluations
        Route::get('/evaluations', [EvaluationController::class, 'teacherEvaluations'])->name('evaluations');
        Route::get('/evaluations/create', [EvaluationController::class, 'create'])->name('evaluations.create');
        Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');
        Route::get('/evaluations/{evaluation}', [EvaluationController::class, 'teacherShow'])->name('evaluations.show');
        Route::get('/evaluations/{evaluation}/edit', [EvaluationController::class, 'edit'])->name('evaluations.edit');
        Route::put('/evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('evaluations.update');
        Route::delete('/evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->name('evaluations.destroy');

        // Saisie des notes
        Route::get('/evaluations/{evaluation}/marks', [MarkController::class, 'teacherCreate'])->name('marks.create');
        Route::post('/evaluations/{evaluation}/marks', [MarkController::class, 'teacherStore'])->name('marks.store');
        Route::get('/marks/{mark}/edit', [MarkController::class, 'teacherEdit'])->name('marks.edit');
        Route::put('/marks/{mark}', [MarkController::class, 'teacherUpdate'])->name('marks.update');
        Route::delete('/marks/{mark}', [MarkController::class, 'destroy'])->name('marks.destroy');

        // Rapports du titulaire
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/class', [ReportController::class, 'classReport'])->name('class');
            Route::get('/students/{student}/bulletin', [ReportController::class, 'generateBulletin'])->name('students.bulletin');
            Route::get('/class-bulletins', [ReportController::class, 'generateClassBulletins'])->name('class-bulletins');
            Route::get('/evaluations/{evaluation}/pv', [ReportController::class, 'generatePV'])->name('evaluations.pv');
        });
    });

    // Routes pour l'enseignant
    Route::middleware([CheckRole::class . ':enseignant'])->prefix('teacher')->name('teacher.')->group(function () {
        // Tableau de bord enseignant
        Route::get('/dashboard', [DashboardController::class, 'teacherdashboard'])->name('dashboard');

        // Mes matières et classes
        Route::get('/my-subjects', [TeacherController::class, 'mySubjects'])->name('my-subjects');
        Route::get('/my-classes', [ClasseController::class, 'myClasses'])->name('my-classes');

        // Évaluations
        Route::get('/evaluations', [EvaluationController::class, 'teacherEvaluations'])->name('evaluations');
        Route::get('/evaluations/create', [EvaluationController::class, 'create'])->name('evaluations.create');
        Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');
        Route::get('/evaluations/{evaluation}', [EvaluationController::class, 'teacherShow'])->name('evaluations.show');
        Route::get('/evaluations/{evaluation}/edit', [EvaluationController::class, 'edit'])->name('evaluations.edit');
        Route::put('/evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('evaluations.update');
        Route::delete('/evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->name('evaluations.destroy');

        // Saisie des notes
        Route::get('/evaluations/{evaluation}/marks', [MarkController::class, 'teacherCreate'])->name('marks.create');
        Route::post('/evaluations/{evaluation}/marks', [MarkController::class, 'teacherStore'])->name('marks.store');
        Route::get('/marks/{mark}/edit', [MarkController::class, 'teacherEdit'])->name('marks.edit');
        Route::put('/marks/{mark}', [MarkController::class, 'teacherUpdate'])->name('marks.update');
        Route::delete('/marks/{mark}', [MarkController::class, 'destroy'])->name('marks.destroy');

        // Rapports enseignant
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/evaluations/{evaluation}/pv', [ReportController::class, 'generatePV'])->name('evaluations.pv');
            Route::get('/class/{classe}/performance', [ReportController::class, 'classPerformance'])->name('class.performance');
        });
    });

    // Routes pour le secrétaire
    Route::middleware([CheckRole::class . ':secretaire'])->prefix('secretary')->name('secretary.')->group(function () {
        // Tableau de bord secrétaire
        Route::get('/dashboard', [DashboardController::class, 'secretarydashboard'])->name('dashboard');

        // Gestion administrative des élèves
        Route::resource('students', StudentController::class);
        Route::get('/students/{student}/bulletin', [ReportController::class, 'generateBulletin'])->name('students.bulletin');
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
        Route::get('/students/export/template', [StudentController::class, 'exportTemplate'])->name('students.export-template');

        // Gestion des classes
        Route::get('/classes', [ClasseController::class, 'index'])->name('classes');
        Route::get('/classes/{classe}', [ClasseController::class, 'show'])->name('classes.show');
        Route::get('/classes/{classe}/bulletins', [ReportController::class, 'generateClassBulletins'])->name('classes.bulletins');

        // Gestion des enseignants
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers');
        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');

        // Documents et archives
        Route::get('/evaluations/{evaluation}/pv', [ReportController::class, 'generatePV'])->name('evaluations.pv');
        Route::get('/archives/students', [StudentController::class, 'archives'])->name('archives.students');
        Route::post('/archives/students/{id}/restore', [StudentController::class, 'restore'])->name('archives.students.restore');
        Route::get('/archives/bulletins', [ReportController::class, 'archivedBulletins'])->name('archives.bulletins');

        // Rapports secrétaire
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/school', [ReportController::class, 'schoolReport'])->name('school');
            Route::get('/classes', [ReportController::class, 'classesReport'])->name('classes');
            Route::get('/performance', [ReportController::class, 'performanceReport'])->name('performance');
            Route::get('/bulletin/{student}', [ReportController::class, 'generateBulletin'])->name('bulletin');
            Route::get('/class-bulletins/{classe}', [ReportController::class, 'generateClassBulletins'])->name('class-bulletins');
        });
    });

    // Routes communes pour la consultation des élèves (accessible à tous les rôles ayant la permission)
    Route::middleware(['can:view-students'])->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    });

    // Routes communes pour la consultation des évaluations
    Route::middleware(['can:view-evaluations'])->group(function () {
        Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
        Route::get('/evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');
    });

    // Routes communes pour les rapports
    Route::prefix('reports')->name('reports.')->middleware(['can:generate-reports'])->group(function () {
        Route::get('/bulletin/{student}', [ReportController::class, 'generateBulletin'])->name('bulletin');
        Route::get('/class-bulletins/{classe}', [ReportController::class, 'generateClassBulletins'])->name('class-bulletins');
        Route::get('/pv/{evaluation}', [ReportController::class, 'generatePV'])->name('pv');
        Route::get('/archived-bulletins', [ReportController::class, 'archivedBulletins'])->name('archived-bulletins');
    });
});

// Routes API pour les données dynamiques
Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Statistiques dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('api.dashboard.stats');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('api.notifications.unread-count');
    Route::get('/notifications/recent', [NotificationController::class, 'getRecentNotifications'])->name('api.notifications.recent');

    // Données dynamiques pour les classes
    Route::get('/classes/{classe}/statistics', [ClasseController::class, 'getStatistics'])->name('api.classes.statistics');
    Route::get('/classes/{classe}/students', [ClasseController::class, 'getStudents'])->name('api.classes.students');

    // Données dynamiques pour les élèves
    Route::get('/students/{student}/progress', [StudentController::class, 'getProgress'])->name('api.students.progress');
    Route::get('/students/{student}/marks', [StudentController::class, 'getMarks'])->name('api.students.marks');

    // Données pour les enseignants
    Route::get('/teachers/{teacher}/assignments', [TeacherController::class, 'getAssignments'])->name('api.teachers.assignments');

    // Recherche rapide
    Route::get('/search/students', [StudentController::class, 'search'])->name('api.search.students');
    Route::get('/search/teachers', [TeacherController::class, 'search'])->name('api.search.teachers');
    Route::get('/search/classes', [ClasseController::class, 'search'])->name('api.search.classes');

    // Données pour les rapports
    Route::get('/reports/class/{classe}/statistics', [ReportController::class, 'getClassStatistics'])->name('api.reports.class-statistics');
    Route::get('/reports/school/statistics', [ReportController::class, 'getSchoolStatistics'])->name('api.reports.school-statistics');
});

Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
    Route::get('/password', [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::get('/activity', [ProfileController::class, 'activity'])->name('activity');
    Route::get('/notifications', [ProfileController::class, 'notifications'])->name('notifications');
});

// Route de fallback pour les pages 404
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});
