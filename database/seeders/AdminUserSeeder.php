<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => 'admin@ecole.com',
            'password' => Hash::make('password123'),
            'matricule' => 'ADM' . date('Y') . '0001',
            'phone' => '+221771234567',
            'is_active' => true,
        ]);
        
        // $admin->isAdministrator();
        $admin->assignRole('administrateur');

        // Créer un directeur de test
        $director = User::create([
            'name' => 'Directeur École',
            'email' => 'directeur@ecole.com',
            'password' => Hash::make('password123'),
            'matricule' => 'DIR' . date('Y') . '0001',
            'phone' => '+221771234568',
            'is_active' => true,
        ]);

        $director->assignRole('directeur');

        // Créer un enseignant titulaire de test
        $titular = User::create([
            'name' => 'Professeur Titulaire',
            'email' => 'titulaire@ecole.com',
            'password' => Hash::make('password123'),
            'matricule' => User::generateMatricule(),
            'phone' => '+221771234569',
            'is_active' => true,
        ]);

        $titular->assignRole('enseignant titulaire');

        // Créer un enseignant de test
        $teacher = User::create([
            'name' => 'Professeur Math',
            'email' => 'math@ecole.com',
            'password' => Hash::make('password123'),
            'matricule' => User::generateMatricule(),
            'phone' => '+221771234570',
            'is_active' => true,
        ]);

        $teacher->assignRole('enseignant');

        // Créer un secrétaire de test
        $secretary = User::create([
            'name' => 'Secrétaire Administrative',
            'email' => 'secretaire@ecole.com',
            'password' => Hash::make('password123'),
            'matricule' => 'SEC' . date('Y') . '0001',
            'phone' => '+221771234571',
            'is_active' => true,
        ]);

        $secretary->assignRole('secretaire');
    }
}
