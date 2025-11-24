<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolYear;

class SchoolYearSeeder extends Seeder
{
    public function run()
    {
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        $schoolYears = [
            [
                'year' => $currentYear . '-' . $nextYear,
                'is_current' => true,
                'start_date' => $currentYear . '-09-01',
                'end_date' => $nextYear . '-07-31',
            ],
            [
                'year' => ($currentYear - 1) . '-' . $currentYear,
                'is_current' => false,
                'start_date' => ($currentYear - 1) . '-09-01',
                'end_date' => $currentYear . '-07-31',
            ]
        ];

        foreach ($schoolYears as $schoolYear) {
            SchoolYear::create($schoolYear);
        }
    }
}
