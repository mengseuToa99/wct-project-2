<?php

namespace Database\Seeders;

use App\Models\reporter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReporterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Reporter::factory()
            ->count(5)
            ->hasReports(1) // each reporter has 1 report
            ->create();
        
    }
}
