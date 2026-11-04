<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst(fake()->unique()->words(3, true));

        return [
            'title' => $title,
            'title_np' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 9999),
            'synopsis' => fake()->paragraph(),
            'synopsis_np' => fake()->paragraph(),
            'genres' => [fake()->randomElement(['Action', 'Drama', 'Comedy', 'Thriller', 'Romance'])],
            'duration_minutes' => fake()->numberBetween(95, 165),
            'language' => fake()->randomElement(['Nepali', 'Hindi', 'English']),
            'release_date' => fake()->dateTimeBetween('-6 months', '+3 months')->format('Y-m-d'),
            'certificate' => fake()->randomElement(['U', 'U/A', 'A']),
            'rating' => fake()->randomFloat(1, 6.0, 9.2),
            'poster_url' => '/img/posters/poster-'.fake()->numberBetween(1, 8).'.svg',
            'banner_url' => '/img/banners/banner-'.fake()->numberBetween(1, 4).'.svg',
            'trailer_url' => null,
            'director' => fake()->name(),
            'cast' => fake()->names(3)->implode(', '),
            'is_now_showing' => true,
            'is_coming_soon' => false,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function nowShowing(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_now_showing' => true,
            'is_coming_soon' => false,
        ]);
    }

    public function comingSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_now_showing' => false,
            'is_coming_soon' => true,
            'release_date' => fake()->dateTimeBetween('+7 days', '+3 months')->format('Y-m-d'),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
