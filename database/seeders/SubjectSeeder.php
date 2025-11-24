<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $subjects = [
            ['name' => 'Mathématiques', 'code' => 'MATH', 'coefficient' => 4],
            ['name' => 'Physique-Chimie', 'code' => 'PHYS', 'coefficient' => 3],
            ['name' => 'Sciences de la Vie et de la Terre', 'code' => 'SVT', 'coefficient' => 3],
            ['name' => 'Français', 'code' => 'FRAN', 'coefficient' => 3],
            ['name' => 'Anglais', 'code' => 'ANGL', 'coefficient' => 2],
            ['name' => 'Histoire-Géographie', 'code' => 'HIST', 'coefficient' => 2],
            ['name' => 'Philosophie', 'code' => 'PHIL', 'coefficient' => 2],
            ['name' => 'Éducation Physique et Sportive', 'code' => 'EPS', 'coefficient' => 1],
            ['name' => 'Informatique', 'code' => 'INFO', 'coefficient' => 2],
            ['name' => 'Économie', 'code' => 'ECON', 'coefficient' => 2],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
