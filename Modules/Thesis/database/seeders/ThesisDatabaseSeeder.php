<?php

namespace Modules\Thesis\Database\Seeders;

use Illuminate\Database\Seeder;

class ThesisDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            StudentStatusSeeder::class,
        ]);
    }
}
