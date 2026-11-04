<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1, 4) * 500;

        return [
            'booking_code' => strtoupper('CG-'.fake()->unique()->bothify('########')),
            'user_id' => User::factory(),
            'movie_id' => Movie::factory(),
            'cinema_id' => Cinema::factory(),
            'hall_id' => Hall::factory(),
            'showtime_id' => Showtime::factory(),
            'show_date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => '19:00:00',
            'movie_title' => fake()->words(3, true),
            'cinema_name' => 'CineGo '.fake()->city(),
            'hall_name' => 'Hall 1',
            'format' => '2D',
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => '+977-98'.fake()->numerify('########'),
            'seat_count' => fake()->numberBetween(1, 4),
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'total_amount' => $subtotal,
            'offer_code' => null,
            'payment_method' => fake()->randomElement(array_keys(Booking::PAYMENT_METHODS)),
            'status' => Booking::STATUS_CONFIRMED,
            'is_paid' => true,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_PENDING,
            'is_paid' => false,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_CANCELLED,
            'is_paid' => false,
        ]);
    }
}
