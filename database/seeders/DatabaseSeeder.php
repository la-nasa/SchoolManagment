<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolePermissionSeeder::class,
            SchoolYearSeeder::class,
            TermSeeder::class,
            ExamTypeSeeder::class,
            SubjectSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
