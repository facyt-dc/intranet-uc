<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StudentStatus; 

class StudentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        StudentStatus::updateOrCreate(
            ['name' => 'inscrito'], 
            [
                'description' => 'Estudiante inscrito',
            ]
        );

        StudentStatus::updateOrCreate(
            ['name' => 'PTEG inscrito'],
            [
                'description' => 'El estudiante ha inscrito PTEG',
            ]
        );

        StudentStatus::updateOrCreate(
            ['name' => 'TEG inscrito'],
            [
                'description' => 'El estudiante ha inscrito TEG',
            ]
        );

    
    }
}