<?php

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst(fake()->unique()->words(3, true)).' Offer';

        return [
            'title' => $title,
            'title_np' => $title,
            'description' => fake()->sentence(),
            'description_np' => fake()->sentence(),
            'code' => strtoupper(fake()->unique()->bothify('CG-####')),
            'badge' => fake()->numberBetween(10, 30).'% OFF',
            'discount_type' => 'percentage',
            'discount_value' => fake()->numberBetween(10, 30),
            'min_amount' => fake()->randomElement([null, 1000, 1500]),
            'valid_from' => now()->subDays(7)->format('Y-m-d'),
            'valid_to' => now()->addDays(30)->format('Y-m-d'),
            'image_url' => null,
            'is_active' => true,
        ];
    }
}
