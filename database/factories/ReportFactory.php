<?php

namespace Database\Factories;

use App\Models\category;
use App\Models\location;
use App\Models\report_detail;
use App\Models\ReportDetail;
use App\Models\reporter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reporter_id' => Reporter::factory()->create()->id,
            'status' => $this->faker->randomElement(['nostatus', 'pending', 'deny', 'complete']),
            'location_id' => Location::factory()->create()->id,
            'report_detail_id' => ReportDetail::factory()->create()->id,
            'category_id' => Category::factory()->create()->id,
        ];
    }
}
