<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamType;

class ExamTypeSeeder extends Seeder
{
    public function run()
    {
        $examTypes = [
            // Séquences
            [
                'name' => 'Séquence 1',
                'code' => 'SEQ1',
                'weight' => 1.0,
                'max_marks' => 20,
                'is_sequence' => true,
                'is_term' => false,
                'order' => 1,
            ],
            [
                'name' => 'Séquence 2',
                'code' => 'SEQ2',
                'weight' => 1.0,
                'max_marks' => 20,
                'is_sequence' => true,
                'is_term' => false,
                'order' => 2,
            ],
            [
                'name' => 'Séquence 3',
                'code' => 'SEQ3',
                'weight' => 1.0,
                'max_marks' => 20,
                'is_sequence' => true,
                'is_term' => false,
                'order' => 3,
            ],
            // Examens de trimestre
            [
                'name' => 'Composition Trimestre 1',
                'code' => 'COMP1',
                'weight' => 2.0,
                'max_marks' => 20,
                'is_sequence' => false,
                'is_term' => true,
                'order' => 4,
            ],
            [
                'name' => 'Composition Trimestre 2',
                'code' => 'COMP2',
                'weight' => 2.0,
                'max_marks' => 20,
                'is_sequence' => false,
                'is_term' => true,
                'order' => 5,
            ],
            [
                'name' => 'Composition Trimestre 3',
                'code' => 'COMP3',
                'weight' => 2.0,
                'max_marks' => 20,
                'is_sequence' => false,
                'is_term' => true,
                'order' => 6,
            ],
            // Autres types d'évaluation
            [
                'name' => 'Devoir Surveillé',
                'code' => 'DS',
                'weight' => 1.5,
                'max_marks' => 20,
                'is_sequence' => false,
                'is_term' => false,
                'order' => 7,
            ],
            [
                'name' => 'Interrogation',
                'code' => 'INT',
                'weight' => 1.0,
                'max_marks' => 10,
                'is_sequence' => false,
                'is_term' => false,
                'order' => 8,
            ]
        ];

        foreach ($examTypes as $examType) {
            ExamType::create($examType);
        }
    }
}
