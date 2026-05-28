<?php

namespace Modules\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Employees\Models\Employee;
use Modules\Employees\Models\Staff;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'cedula' => $this->faker->unique()->numerify('##########'),
            'address' => $this->faker->address(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'birthday' => $this->faker->date('Y-m-d', '-20 years'),
            'staff' => StaffFactory::new()->create()->id,
            'teaching_level' => null,
        ];
    }
}
