<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\report_detail>
 */
class ReportDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'anonymous' => $this->faker->boolean, // generates a random boolean (true or false)
            'image' => $this->faker->imageUrl(640, 480, 'place'),
            'feedback' => $this->faker->sentence(2),
        ];
    }
}
