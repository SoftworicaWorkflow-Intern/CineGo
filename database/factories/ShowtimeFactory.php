<?php

namespace Database\Factories;

use App\Models\Cinema;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Showtime>
 */
class ShowtimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $normal = fake()->randomElement([400, 450, 500, 550, 600, 700]);

        return [
            'movie_id' => Movie::factory(),
            'cinema_id' => Cinema::factory(),
            'hall_id' => Hall::factory(),
            'show_date' => now()->addDays(fake()->numberBetween(0, 6))->format('Y-m-d'),
            'start_time' => fake()->randomElement(['10:30:00', '13:15:00', '16:00:00', '19:00:00', '21:30:00']),
            'format' => fake()->randomElement(['2D', '2D', '2D', '3D']),
            'language' => fake()->randomElement(['Nepali', 'Hindi', 'English']),
            'price_normal' => $normal,
            'price_premium' => $normal + 100,
            'price_recliner' => $normal + 250,
            'is_active' => true,
        ];
    }
}
