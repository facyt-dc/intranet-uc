<?php

namespace Modules\Thesis\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Thesis\Models\StudentStatus;

class StudentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'inscrito', 'description' => 'Estudiante inscrito'],
            ['name' => 'PTEG inscrito', 'description' => 'El estudiante ha inscrito PTEG'],
            ['name' => 'TEG inscrito', 'description' => 'El estudiante ha inscrito TEG'],
            ['name' => 'PTEG aprobado', 'description' => 'El estudiante ha aprobado PTEG'],
            ['name' => 'TEG aprobado', 'description' => 'El estudiante ha aprobado TEG'],
        ];

        foreach ($statuses as $status) {
            StudentStatus::firstOrCreate(['name' => $status['name']], ['description' => $status['description']]);
        }
    }
}