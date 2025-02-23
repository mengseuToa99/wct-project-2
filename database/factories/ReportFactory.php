<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Location;
use App\Models\ReportDetail;
use App\Models\Reporter;
use App\Models\TypeOfCategory;
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
            'typeOfCategory_id' => TypeOfCategory::factory()->create()->id,
        ];
    }
}
