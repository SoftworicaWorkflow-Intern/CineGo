<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a linked cinema → hall → seats → movie → showtime chain.
     * Seats A1–A2 are normal (Rs. 500), A3–A4 premium (Rs. 700).
     */
    private function makeShowtime(): Showtime
    {
        $cinema = Cinema::factory()->create();
        $hall = Hall::factory()->create([
            'cinema_id' => $cinema->id,
            'total_rows' => 2,
            'seats_per_row' => 4,
        ]);

        foreach ([1, 2, 3, 4] as $number) {
            Seat::factory()->create([
                'hall_id' => $hall->id,
                'row_label' => 'A',
                'seat_number' => $number,
                'seat_type' => $number <= 2 ? 'normal' : 'premium',
                'is_active' => true,
            ]);
        }

        $movie = Movie::factory()->create();

        return Showtime::factory()->create([
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'hall_id' => $hall->id,
            'show_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '19:00:00',
            'price_normal' => 500,
            'price_premium' => 700,
            'price_recliner' => 900,
            'is_active' => true,
        ]);
    }

    private function seat(Showtime $showtime, int $number): Seat
    {
        return $showtime->hall->seats()
            ->where('row_label', 'A')
            ->where('seat_number', $number)
            ->firstOrFail();
    }

    public function test_seat_map_page_renders(): void
    {
        $showtime = $this->makeShowtime();

        $this->get(route('booking.seats', $showtime))
            ->assertOk()
            ->assertSee($showtime->movie->title);
    }

    public function test_selecting_no_seats_is_rejected(): void
    {
        $showtime = $this->makeShowtime();

        $this->from(route('booking.seats', $showtime))
            ->post(route('booking.select', $showtime), [])
            ->assertSessionHasErrors('seats');
    }

    public function test_selecting_seats_holds_them_and_opens_checkout(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 1);

        $this->post(route('booking.select', $showtime), ['seats' => [$seat->id]])
            ->assertRedirect(route('booking.checkout', $showtime));

        $this->assertDatabaseHas('seat_locks', [
            'showtime_id' => $showtime->id,
            'seat_id' => $seat->id,
        ]);

        $this->get(route('booking.checkout', $showtime))->assertOk();
    }

    public function test_checkout_requires_a_live_seat_hold(): void
    {
        $showtime = $this->makeShowtime();

        $this->get(route('booking.checkout', $showtime))
            ->assertRedirect(route('booking.seats', $showtime));
    }

    public function test_guest_can_complete_a_counter_booking(): void
    {
        $showtime = $this->makeShowtime();
        $seats = [$this->seat($showtime, 1)->id, $this->seat($showtime, 2)->id];

        $this->post(route('booking.select', $showtime), ['seats' => $seats])
            ->assertRedirect(route('booking.checkout', $showtime));

        $this->post(route('booking.place', $showtime), [
            'customer_name' => 'Sita Sharma',
            'customer_email' => 'sita@example.com',
            'customer_phone' => '9800000000',
            'payment_method' => 'counter',
        ])->assertRedirect();

        $booking = Booking::sole();
        $this->assertSame(Booking::STATUS_CONFIRMED, $booking->status);
        $this->assertFalse((bool) $booking->is_paid);
        $this->assertSame(2, (int) $booking->seat_count);
        $this->assertSame(1000.0, (float) $booking->total_amount);
        $this->assertSame('Sita Sharma', $booking->customer_name);

        // The digital ticket (with QR) renders for the booking code.
        $this->get(route('booking.ticket', $booking->booking_code))
            ->assertOk()
            ->assertSee($booking->booking_code);

        // The PDF download works too.
        $this->get(route('booking.download', $booking->booking_code))->assertOk();
    }

    public function test_online_payment_confirms_the_booking_after_verification(): void
    {
        $showtime = $this->makeShowtime();

        $this->post(route('booking.select', $showtime), ['seats' => [$this->seat($showtime, 3)->id]])
            ->assertRedirect(route('booking.checkout', $showtime));

        $this->post(route('booking.place', $showtime), [
            'customer_name' => 'Aayush Gurung',
            'customer_phone' => '9811111111',
            'payment_method' => 'esewa',
        ])->assertRedirect();

        $payment = Payment::sole();
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        // The amount comes from the server-side seat price (premium = 700).
        $this->assertSame(700.0, (float) $payment->amount);
        $this->assertSame(Booking::STATUS_PENDING, $payment->booking->status);

        // The simulated provider page is reachable for this guest session.
        $this->get(route('payment.process', $payment))->assertOk();

        $this->post(route('payment.verify', $payment))->assertRedirect();

        $payment->refresh();
        $this->assertSame(Payment::STATUS_SUCCESS, $payment->status);
        $this->assertNotNull($payment->verified_at);
        $this->assertSame(Booking::STATUS_CONFIRMED, $payment->booking->status);
        $this->assertTrue((bool) $payment->booking->is_paid);
    }

    public function test_payment_verification_is_idempotent(): void
    {
        $showtime = $this->makeShowtime();

        $this->post(route('booking.select', $showtime), ['seats' => [$this->seat($showtime, 4)->id]])->assertRedirect();
        $this->post(route('booking.place', $showtime), [
            'customer_name' => 'Khalti Fan',
            'customer_phone' => '9822222222',
            'payment_method' => 'khalti',
        ])->assertRedirect();

        $payment = Payment::sole();

        $this->post(route('payment.verify', $payment))->assertRedirect();
        $this->post(route('payment.verify', $payment))->assertRedirect();

        $this->assertSame(1, Payment::count());
        $this->assertSame(Payment::STATUS_SUCCESS, $payment->fresh()->status);
        $this->assertSame(Booking::STATUS_CONFIRMED, Booking::sole()->status);
    }

    public function test_duplicate_seat_booking_is_blocked(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 1);
        $service = app(BookingService::class);
        $customer = ['customer_name' => 'First Buyer', 'customer_phone' => '9800000001'];

        $service->createBooking($showtime, [$seat->id], $customer, null, null, Booking::STATUS_CONFIRMED);

        try {
            $service->createBooking($showtime, [$seat->id], $customer, null, null, Booking::STATUS_CONFIRMED);
            $this->fail('A second booking for the same seat should be rejected.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('seats', $e->errors());
        }

        $this->assertSame(1, Booking::count());
    }

    public function test_seats_from_another_screen_are_rejected(): void
    {
        $showtime = $this->makeShowtime();
        $other = $this->makeShowtime();

        try {
            app(BookingService::class)->createBooking($showtime, [$this->seat($other, 1)->id], [
                'customer_name' => 'Intruder',
                'customer_phone' => '9800000002',
            ]);
            $this->fail('A seat from another hall should be rejected.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('seats', $e->errors());
        }

        $this->assertSame(0, Booking::count());
    }

    public function test_totals_are_calculated_on_the_server(): void
    {
        $showtime = $this->makeShowtime();

        $totals = app(BookingService::class)->totals($showtime, [
            $this->seat($showtime, 1)->id, // normal 500
            $this->seat($showtime, 3)->id, // premium 700
        ]);

        $this->assertSame(1200.0, $totals['subtotal']);
        $this->assertSame(0.0, $totals['discount']);
        $this->assertSame(1200.0, $totals['total']);
    }

    public function test_expired_pending_bookings_release_their_seats(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 4);
        $service = app(BookingService::class);

        $booking = $service->createBooking($showtime, [$seat->id], [
            'customer_name' => 'Slow Payer',
            'customer_phone' => '9800000003',
        ]);

        Booking::query()->whereKey($booking->id)->update([
            'created_at' => now()->subMinutes(BookingService::PENDING_TTL_MINUTES + 1),
        ]);

        $service->releaseStaleBookings($showtime);

        $this->assertSame(Booking::STATUS_CANCELLED, $booking->fresh()->status);

        // The seat is bookable again after the payment window expires.
        $again = $service->createBooking($showtime, [$seat->id], [
            'customer_name' => 'Second Buyer',
            'customer_phone' => '9800000004',
        ], null, null, Booking::STATUS_CONFIRMED);

        $this->assertSame(Booking::STATUS_CONFIRMED, $again->status);
    }

    public function test_authenticated_customer_booking_is_linked_to_their_account(): void
    {
        $user = User::factory()->create();
        $showtime = $this->makeShowtime();

        $this->actingAs($user)
            ->post(route('booking.select', $showtime), ['seats' => [$this->seat($showtime, 2)->id]])
            ->assertRedirect(route('booking.checkout', $showtime));

        $this->actingAs($user)
            ->post(route('booking.place', $showtime), [
                'customer_name' => $user->name,
                'customer_phone' => '9833333333',
                'payment_method' => 'counter',
            ])
            ->assertRedirect();

        $booking = Booking::sole();
        $this->assertSame($user->id, (int) $booking->user_id);
    }

    public function test_cancelled_booking_frees_the_seat(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 1);
        $service = app(BookingService::class);

        $booking = $service->createBooking($showtime, [$seat->id], [
            'customer_name' => 'Canceller',
            'customer_phone' => '9800000005',
        ], null, null, Booking::STATUS_CONFIRMED);

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        $again = $service->createBooking($showtime, [$seat->id], [
            'customer_name' => 'Lucky Buyer',
            'customer_phone' => '9800000006',
        ], null, null, Booking::STATUS_CONFIRMED);

        $this->assertSame(Booking::STATUS_CONFIRMED, $again->status);
        $this->assertSame(2, Booking::count());
    }
}
