<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions
        $permissions = [
            // Gestion des utilisateurs
            'view-users', 'create-users', 'edit-users', 'delete-users',
            // Gestion des élèves
            'view-students', 'create-students', 'edit-students', 'delete-students',
            // Gestion des classes
            'view-classes', 'create-classes', 'edit-classes', 'delete-classes',
            // Gestion des matières
            'view-subjects', 'create-subjects', 'edit-subjects', 'delete-subjects',
            // Gestion des notes
            'view-marks', 'create-marks', 'edit-marks', 'delete-marks',
            // Gestion des évaluations
            'view-evaluations', 'create-evaluations', 'edit-evaluations', 'delete-evaluations',
            // Tableaux de bord
            'view-dashboard-admin', 'view-dashboard-director', 'view-dashboard-teacher',
            'view-dashboard-titular', 'view-dashboard-secretary',
            // Rapports
            'view-reports', 'generate-reports', 'export-reports',
            // Audit
            'view-audit-trail', 'export-audit-trail',
            // Configuration
            'manage-settings', 'manage-school-years', 'manage-terms',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Créer les rôles et assigner les permissions
        $adminRole = Role::create(['name' => 'administrateur']);
        $adminRole->givePermissionTo(Permission::all());

        $directorRole = Role::create(['name' => 'directeur']);
        $directorPermissions = [
            'view-dashboard-director', 'view-users', 'view-students', 'view-classes',
            'view-subjects', 'view-marks', 'view-evaluations', 'view-reports',
            'generate-reports', 'export-reports', 'view-audit-trail'
        ];
        $directorRole->givePermissionTo($directorPermissions);

        $titularRole = Role::create(['name' => 'enseignant titulaire']);
        $titularPermissions = [
            'view-dashboard-titular', 'view-students', 'view-classes', 'view-marks',
            'view-evaluations', 'create-marks', 'edit-marks', 'view-reports'
        ];
        $titularRole->givePermissionTo($titularPermissions);

        $teacherRole = Role::create(['name' => 'enseignant']);
        $teacherPermissions = [
            'view-dashboard-teacher', 'view-students', 'view-marks', 'view-evaluations',
            'create-marks', 'edit-marks'
        ];
        $teacherRole->givePermissionTo($teacherPermissions);

        $secretaryRole = Role::create(['name' => 'secretaire']);
        $secretaryPermissions = [
            'view-dashboard-secretary', 'view-students', 'view-classes', 'view-users',
            'create-students', 'edit-students', 'view-reports', 'generate-reports', 'export-reports'
        ];
        $secretaryRole->givePermissionTo($secretaryPermissions);
    }
}
