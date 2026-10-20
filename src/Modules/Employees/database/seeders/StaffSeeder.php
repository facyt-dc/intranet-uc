<?php

namespace Modules\Employees\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employees\Models\Benefit;
use Modules\Employees\Models\Staff;
use Modules\Employees\Models\StaffType;
use Modules\Employees\Models\TeachingLevel;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacher        = StaffType::firstOrCreate(['name' => 'Docente']);
        $administrative = StaffType::firstOrCreate(['name' => 'Administrativo']);

        // Cargos
        $staffs = [
            ['name' => 'Director', 'places_number' => 1, 'type' => $teacher->id, 'teaching' => true],
            ['name' => 'Profesor', 'places_number' => 20, 'type' => $teacher->id, 'teaching' => true],
            ['name' => 'Coordinador', 'places_number' => 5, 'type' => $teacher->id, 'teaching' => true],
            ['name' => 'Asistente Administrativo', 'places_number' => 5, 'type' => $administrative->id, 'teaching' => false],
        ];

        $benefits       = Benefit::pluck('id');
        $teachingLevels = TeachingLevel::pluck('id');

        foreach ($staffs as $staff) {
            $record = Staff::firstOrCreate(['name' => $staff['name']], [
                'places_number' => $staff['places_number'],
                'type' => $staff['type'],
            ]);

            $record->benefits()->syncWithoutDetaching($benefits);

            if ($staff['teaching']) {
                $record->teaching_levels()->syncWithoutDetaching($teachingLevels);
            }
        }
    }
}
