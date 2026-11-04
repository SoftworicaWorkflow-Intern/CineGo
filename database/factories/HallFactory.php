<?php

namespace Database\Factories;

use App\Models\Cinema;
use App\Models\Hall;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hall>
 */
class HallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cinema_id' => Cinema::factory(),
            'name' => 'Hall '.fake()->numberBetween(1, 4),
            'total_rows' => fake()->numberBetween(6, 10),
            'seats_per_row' => fake()->numberBetween(10, 14),
            'is_active' => true,
        ];
    }
}
