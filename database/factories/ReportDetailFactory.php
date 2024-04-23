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
            'profile_pic' => $this->faker->imageUrl(640, 480, 'places'),
            'anonymous' => $this->faker->boolean, // generates a random boolean (true or false)
            'feedback' => $this->faker->sentence(2),
        ];
    }
}
