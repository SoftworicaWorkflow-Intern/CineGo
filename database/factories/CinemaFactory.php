<?php

namespace Database\Factories;

use App\Models\Cinema;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cinema>
 */
class CinemaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'CineGo '.fake()->unique()->city();

        return [
            'city_id' => City::factory(),
            'name' => $name,
            'name_np' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'address' => fake()->streetAddress(),
            'address_np' => fake()->streetAddress(),
            'phone' => '+977-98'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'description' => fake()->paragraph(),
            'description_np' => fake()->paragraph(),
            'facilities' => ['Parking', 'Snacks', 'Air Conditioned'],
            'image_url' => null,
            'is_active' => true,
        ];
    }
}
