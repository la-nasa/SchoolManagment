<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Term;
use App\Models\SchoolYear;

class TermSeeder extends Seeder
{
    public function run()
    {
        $currentSchoolYear = SchoolYear::where('is_current', true)->first();

        if (!$currentSchoolYear) {
            return;
        }

        $terms = [
            [
                'name' => 'Premier Trimestre',
                'order' => 1,
                'start_date' => $currentSchoolYear->start_date,
                'end_date' => date('Y-12-20'),
                'is_current' => true,
                'school_year_id' => $currentSchoolYear->id,
            ],
            [
                'name' => 'Deuxième Trimestre',
                'order' => 2,
                'start_date' => date('Y-01-08'),
                'end_date' => date('Y-04-10'),
                'is_current' => false,
                'school_year_id' => $currentSchoolYear->id,
            ],
            [
                'name' => 'Troisième Trimestre',
                'order' => 3,
                'start_date' => date('Y-04-20'),
                'end_date' => $currentSchoolYear->end_date,
                'is_current' => false,
                'school_year_id' => $currentSchoolYear->id,
            ]
        ];

        foreach ($terms as $term) {
            Term::create($term);
        }
    }
}
