<?php

namespace Modules\Thesis\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Thesis\Models\StudentStatus;
use Modules\Thesis\Models\ThesisStudent;

class StudentStatusHistoryFactory extends Factory
{
    protected $model = \Modules\Thesis\Models\StudentStatusHistory::class;

    public function definition(): array
    {
        return [
            'thesis_student_id' => ThesisStudent::factory(),
            'student_status_id' => StudentStatus::factory(),
            'start_date' => $this->faker->date(),
        ];
    }
}