<?php

namespace Modules\AgendaConsejo\Database\Seeders;

use Illuminate\Database\Seeder;

class AgendaConsejoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            VotingOptionsSeeder::class
        ]);
    }
}
