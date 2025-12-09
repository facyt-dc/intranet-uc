<?php

namespace Modules\AgendaConsejo\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AgendaConsejo\Models\VotingOption;

class VotingOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VotingOption::firstOrCreate(['name' => 'A favor']);
        VotingOption::firstOrCreate(['name' => 'En contra']);
    }
}
