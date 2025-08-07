<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VotingOption;

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
