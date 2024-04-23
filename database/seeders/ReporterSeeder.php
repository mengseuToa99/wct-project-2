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
        reporter::factory()
        ->count(5)
        ->hasReports(5)
        ->create();
        
    }
}
