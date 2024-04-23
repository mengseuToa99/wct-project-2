<?php

namespace Database\Factories;

use App\Models\category;
use App\Models\location;
use App\Models\report_detail;
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
            'reporter_id' => reporter::factory()->create()->id,
            'status' => $this->faker->randomElement(['nostatus', 'pending', 'deny', 'complete']),
            'location_id' => location::factory()->create()->id,
            'report_detail_id' => report_detail::factory()->create()->id,
            'category_id' => category::factory()->create()->id,
        ];
    }
}
