<?php

namespace Database\Factories;

use App\Models\Hall;
use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seat>
 */
class SeatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hall_id' => Hall::factory(),
            'row_label' => strtoupper(fake()->randomLetter()),
            'seat_number' => fake()->numberBetween(1, 20),
            'seat_type' => fake()->randomElement(['normal', 'normal', 'premium', 'recliner']),
            'is_active' => true,
        ];
    }
}
