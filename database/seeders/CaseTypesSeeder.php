<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaseType;

class CaseTypesSeeder extends Seeder
{
    public function run(): void
    {
        $caseTypes = [
            ['name' => 'قضية تعويض', 'description' => 'قضايا المطالبة بالتعويضات'],
            ['name' => 'قضية إيجار', 'description' => 'قضايا النزاعات السكنية والتجارية'],
            ['name' => 'قضية تجارية', 'description' => 'القضايا المتعلقة بالعقود والمعاملات التجارية'],
            ['name' => 'قضية أحوال شخصية', 'description' => 'الزواج والطلاق والنفقة والحضانة'],
            ['name' => 'قضية جنائية', 'description' => 'القضايا الجنائية'],
            ['name' => 'قضية إدارية', 'description' => 'القضايا المتعلقة بالإدارة والموظفين'],
        ];

        foreach ($caseTypes as $caseType) {
            CaseType::firstOrCreate(['name' => $caseType['name']], $caseType);
        }
    }
}
