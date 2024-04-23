<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\reporter>
 */
class ReporterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->username(),
            'profile_pic' => $this->faker->imageUrl(640, 480, 'people'),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),

        ];
    }
}
