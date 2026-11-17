<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardChartsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_eight_charts_with_booking_and_payment_data(): void
    {
        $this->travelTo('2026-11-17 12:00:00');
        $admin = User::factory()->create(['is_admin' => true]);
        $confirmedBooking = Booking::factory()->create([
            'movie_title' => 'Neon Horizon',
            'cinema_name' => 'CineGo Central',
            'seat_count' => 3,
            'payment_method' => 'esewa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $pendingBooking = Booking::factory()->pending()->create([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cancelledBooking = Booking::factory()->cancelled()->create([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Payment::query()->create([
            'booking_id' => $confirmedBooking->id,
            'user_id' => $confirmedBooking->user_id,
            'provider' => 'esewa',
            'reference' => 'PAY-CHART-SUCCESS',
            'amount' => 850,
            'status' => Payment::STATUS_SUCCESS,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Payment::query()->create([
            'booking_id' => $pendingBooking->id,
            'user_id' => $pendingBooking->user_id,
            'provider' => 'khalti',
            'reference' => 'PAY-CHART-PENDING',
            'amount' => 300,
            'status' => Payment::STATUS_PENDING,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Payment::query()->create([
            'booking_id' => $cancelledBooking->id,
            'user_id' => $cancelledBooking->user_id,
            'provider' => 'card',
            'reference' => 'PAY-CHART-FAILED',
            'amount' => 100,
            'status' => Payment::STATUS_FAILED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertViewHas('charts', function (array $charts): bool {
                return array_keys($charts) === [
                    'booking_trend',
                    'booking_status',
                    'payment_outcomes',
                    'tickets_by_movie',
                    'revenue_by_movie',
                    'bookings_by_cinema',
                    'bookings_by_weekday',
                    'payment_methods',
                ]
                    && $charts['booking_trend']['bookings'][13] === 3
                    && $charts['booking_status']['values'] === [1, 1, 1]
                    && $charts['payment_outcomes']['values'] === [1, 1, 1]
                    && $charts['tickets_by_movie']['values'] === [3]
                    && $charts['revenue_by_movie']['values'] === [850.0]
                    && $charts['bookings_by_cinema']['values'] === [1]
                    && $charts['bookings_by_weekday']['values'] === [0, 3, 0, 0, 0, 0, 0]
                    && $charts['payment_methods']['labels'] === ['eSewa']
                    && $charts['payment_methods']['values'] === [1];
            });

        foreach ([
            'booking_trend',
            'booking_status',
            'payment_outcomes',
            'tickets_by_movie',
            'revenue_by_movie',
            'bookings_by_cinema',
            'bookings_by_weekday',
            'payment_methods',
        ] as $chartKey) {
            $response->assertSee('data-dashboard-chart="'.$chartKey.'"', false);
        }

        $response->assertSee('data-chart-type="pie"', false)
            ->assertSee('data-chart-type="doughnut"', false)
            ->assertSee('data-chart-type="bar"', false);
    }
}
