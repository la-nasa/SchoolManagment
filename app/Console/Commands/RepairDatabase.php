<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class RepairDatabase extends Command
{
    protected $signature = 'db:repair';
    protected $description = 'Réparer les problèmes de base de données';

    public function handle()
    {
        $this->info('=== RÉPARATION DE LA BASE DE DONNÉES ===');

        // 1. Vérifier et créer les tables manquantes
        $this->info("\n1. Vérification des tables...");
        $this->checkAndCreateTables();

        // 2. Vérifier et créer les colonnes manquantes
        $this->info("\n2. Vérification des colonnes...");
        $this->checkAndCreateColumns();

        // 3. Vérifier les relations
        $this->info("\n3. Vérification des relations...");
        $this->checkRelations();

        // 4. Créer des données de test si nécessaire
        $this->info("\n4. Création de données de test...");
        $this->createTestData();

        $this->info("\n=== RÉPARATION TERMINÉE ===");
        return 0;
    }

    private function checkAndCreateTables()
    {
        $tables = [
            'users',
            'classes',
            'students',
            'subjects',
            'terms',
            'school_years',
            'evaluations',
            'marks',
            'averages',
            'general_averages',
            'teacher_assignments',
            'bulletin'
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->warn("Table {$table} manquante. Création...");
                $this->createTable($table);
            } else {
                $this->info("✓ Table {$table} existe");
            }
        }
    }

    private function createTable($tableName)
    {
        switch ($tableName) {
            case 'users':
                Schema::create('users', function ($table) {
                    $table->id();
                    $table->string('first_name');
                    $table->string('last_name');
                    $table->string('email')->unique();
                    $table->string('password');
                    $table->string('matricule')->nullable();
                    $table->string('phone')->nullable();
                    $table->string('address')->nullable();
                    $table->date('birth_date')->nullable();
                    $table->enum('gender', ['M', 'F'])->nullable();
                    $table->string('photo')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->rememberToken();
                    $table->timestamps();
                });
                break;

            case 'classes':
                Schema::create('classes', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->string('level')->nullable();
                    $table->string('section')->nullable();
                    $table->integer('capacity')->default(40);
                    $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
                    $table->foreignId('school_year_id')->nullable()->constrained('school_years')->onDelete('set null');
                    $table->text('description')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                });
                break;
        }
    }

    private function checkAndCreateColumns()
    {
        // Ajouter teacher_id à classes si manquant
        if (Schema::hasTable('classes') && !Schema::hasColumn('classes', 'teacher_id')) {
            $this->warn("Colonne teacher_id manquante dans classes. Ajout...");
            Schema::table('classes', function ($table) {
                $table->foreignId('teacher_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            });
        }

        // Ajouter class_id à students si manquant
        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'class_id')) {
            $this->warn("Colonne class_id manquante dans students. Ajout...");
            Schema::table('students', function ($table) {
                $table->foreignId('class_id')->nullable()->after('id')->constrained('classes')->onDelete('set null');
            });
        }
    }

    private function checkRelations()
    {
        $this->info("Vérification des relations clés étrangères...");

        // Vérifier les étudiants sans classe
        $orphanStudents = DB::table('students')->whereNull('class_id')->count();
        if ($orphanStudents > 0) {
            $this->warn("{$orphanStudents} étudiant(s) sans classe assignée");

            // Assigner à une classe par défaut
            $defaultClass = DB::table('classes')->first();
            if ($defaultClass) {
                DB::table('students')->whereNull('class_id')->update(['class_id' => $defaultClass->id]);
                $this->info("Étudiants assignés à la classe {$defaultClass->name}");
            }
        }

        // Vérifier les classes sans année scolaire
        $orphanClasses = DB::table('classes')->whereNull('school_year_id')->count();
        if ($orphanClasses > 0) {
            $this->warn("{$orphanClasses} classe(s) sans année scolaire");

            // Assigner à l'année scolaire courante
            $currentYear = DB::table('school_years')->where('is_current', true)->first();
            if ($currentYear) {
                DB::table('classes')->whereNull('school_year_id')->update(['school_year_id' => $currentYear->id]);
                $this->info("Classes assignées à l'année scolaire {$currentYear->year}");
            }
        }
    }

    private function createTestData()
    {
        // Créer une année scolaire si aucune n'existe
        if (DB::table('school_years')->count() == 0) {
            $this->warn("Aucune année scolaire. Création...");
            DB::table('school_years')->insert([
                'year' => date('Y') . '-' . (date('Y') + 1),
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'is_current' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Créer un trimestre si aucun n'existe
        if (DB::table('terms')->count() == 0) {
            $this->warn("Aucun trimestre. Création...");
            DB::table('terms')->insert([
                [
                    'name' => 'Premier Trimestre',
                    'order' => 1,
                    'start_date' => now(),
                    'end_date' => now()->addMonths(3),
                    'is_current' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Deuxième Trimestre',
                    'order' => 2,
                    'start_date' => now()->addMonths(3),
                    'end_date' => now()->addMonths(6),
                    'is_current' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        // Créer une classe de test si aucune n'existe
        if (DB::table('classes')->count() == 0) {
            $this->warn("Aucune classe. Création...");
            $schoolYear = DB::table('school_years')->first();

            DB::table('classes')->insert([
                'name' => '6ème A',
                'level' => '6ème',
                'capacity' => 40,
                'school_year_id' => $schoolYear->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Créer un enseignant de test si aucun n'existe
        if (DB::table('users')->where('email', 'like', '%enseignant%')->count() == 0) {
            $this->warn("Aucun enseignant. Création...");
            DB::table('users')->insert([
                'name' => 'Martini Jean',

                'email' => 'enseignant@test.com',
                'password' => bcrypt('password123'),
                'matricule' => 'ENS' . date('y') . '0001',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
