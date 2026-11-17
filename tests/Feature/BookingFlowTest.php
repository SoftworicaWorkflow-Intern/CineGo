<?php

namespace Tests\Feature;

use App\Events\SeatAvailabilityChanged;
use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\SeatLock;
use App\Models\Showtime;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a linked cinema → hall → seats → movie → showtime chain.
     * Every seat uses the normal ticket price of Rs. 500.
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
                'seat_type' => 'normal',
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
            ->assertSee($showtime->movie->title)
            ->assertSee('data-no-scroll-reveal', false)
            ->assertSee('id="seat-form"', false)
            ->assertSee('aria-label="A1 — Available"', false)
            ->assertDontSee('<dt class="text-cg-muted">Cinemas</dt>', false);
    }

    public function test_todays_showtime_opens_the_seat_map_only_before_it_starts(): void
    {
        $this->travelTo(now()->setTime(16, 0));

        $showtime = $this->makeShowtime();
        $showtime->update([
            'show_date' => today()->toDateString(),
            'start_time' => '17:00:00',
        ]);

        $this->get(route('booking.seats', $showtime))
            ->assertOk()
            ->assertSee($showtime->movie->title);

        $showtime->update(['start_time' => '15:00:00']);

        $this->get(route('booking.seats', $showtime))
            ->assertNotFound();
    }

    public function test_selecting_no_seats_is_rejected(): void
    {
        $showtime = $this->makeShowtime();

        $this->from(route('booking.seats', $showtime))
            ->post(route('booking.select', $showtime), [])
            ->assertSessionHasErrors('seats');
    }

    public function test_selecting_seats_broadcasts_their_live_lock(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 1);
        Event::fake([SeatAvailabilityChanged::class]);

        $this->post(route('booking.select', $showtime), ['seats' => [$seat->id]])
            ->assertRedirect(route('booking.checkout', $showtime));

        Event::assertDispatched(SeatAvailabilityChanged::class, fn (SeatAvailabilityChanged $event): bool => (
            $event->showtimeId === $showtime->id
            && $event->seatIds === [$seat->id]
            && $event->status === 'locked'
            && $event->expiresAt !== null
        ));
    }

    public function test_a_live_seat_lock_cannot_be_taken_from_another_holder(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 1);
        SeatLock::query()->create([
            'showtime_id' => $showtime->id,
            'seat_id' => $seat->id,
            'holder' => 'first-customer',
            'expires_at' => now()->addMinutes(SeatLock::TTL_MINUTES),
        ]);

        $conflicts = app(BookingService::class)->lockSeats($showtime, [$seat->id], 'second-customer');

        $this->assertSame([$seat->id], $conflicts);
        $this->assertDatabaseHas('seat_locks', [
            'showtime_id' => $showtime->id,
            'seat_id' => $seat->id,
            'holder' => 'first-customer',
        ]);
    }

    public function test_expired_seat_locks_broadcast_availability(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 1);
        SeatLock::query()->create([
            'showtime_id' => $showtime->id,
            'seat_id' => $seat->id,
            'holder' => 'expired-customer',
            'expires_at' => now()->subSecond(),
        ]);
        Event::fake([SeatAvailabilityChanged::class]);

        SeatLock::purgeExpired();

        $this->assertDatabaseMissing('seat_locks', ['seat_id' => $seat->id]);
        Event::assertDispatched(SeatAvailabilityChanged::class, fn (SeatAvailabilityChanged $event): bool => (
            $event->showtimeId === $showtime->id
            && $event->seatIds === [$seat->id]
            && $event->status === 'available'
        ));
    }

    public function test_seat_availability_event_uses_the_public_showtime_channel(): void
    {
        $event = new SeatAvailabilityChanged(42, [5, 6], 'locked', '2026-11-07T10:10:00+00:00');

        $this->assertSame('showtime.42', $event->broadcastOn()[0]->name);
        $this->assertSame([
            'showtime_id' => 42,
            'seat_ids' => [5, 6],
            'status' => 'locked',
            'expires_at' => '2026-11-07T10:10:00+00:00',
        ], $event->broadcastWith());
    }

    public function test_selecting_seats_holds_them_and_opens_checkout(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 1);

        $seatResponse = $this->get(route('booking.seats', $showtime));

        $seatResponse
            ->assertOk()
            ->assertSee('Screen this way')
            ->assertSee('border-cg-red/40 bg-cg-red/10 text-sm font-bold text-cg-white', false)
            ->assertSee('<span aria-hidden="true" class="cg-seat-number">A1</span>', false)
            ->assertSee('<span class="cg-seat-column-label flex h-8 w-8 items-center justify-center sm:h-9 sm:w-9">1</span>', false)
            ->assertSee('<span class="cg-seat-column-label flex h-8 w-8 items-center justify-center sm:h-9 sm:w-9">2</span>', false)
            ->assertSee('<span class="cg-seat-column-label flex h-8 w-8 items-center justify-center sm:h-9 sm:w-9">3</span>', false)
            ->assertSee('data-hold-seconds="300"', false)
            ->assertSee('<svg', false)
            ->assertDontSee('Seat number')
            ->assertDontSee('>Locked<', false);
        $this->assertSame(1, substr_count($seatResponse->getContent(), 'shadow-sm">A</span>'));

        $this->post(route('booking.select', $showtime), ['seats' => [$seat->id]])
            ->assertRedirect(route('booking.checkout', $showtime));

        $this->assertDatabaseHas('seat_locks', [
            'showtime_id' => $showtime->id,
            'seat_id' => $seat->id,
        ]);

        $checkoutResponse = $this->get(route('booking.checkout', $showtime));
        $checkoutResponse
            ->assertOk()
            ->assertSee('eSewa')
            ->assertSee('Khalti')
            ->assertSee('payments/esewa.png')
            ->assertSee('payments/khalti.png')
            ->assertDontSee('Credit / Debit Card')
            ->assertDontSee('payments/visa.svg')
            ->assertDontSee('payments/mastercard.svg')
            ->assertSee('class="input bg-transparent"', false)
            ->assertSee('class="label font-bold" for="customer_name"', false)
            ->assertSee('class="label font-bold" for="customer_email"', false)
            ->assertSee('class="label font-bold" for="customer_phone"', false)
            ->assertSee('class="mb-4 flex items-center gap-2 font-bold text-cg-white"', false)
            ->assertDontSee('Pay at Counter')
            ->assertDontSee('Pay at the counter')
            ->assertSee('border-2 border-cg-red/40', false)
            ->assertSee('font-bold', false);
        $this->assertSame(3, substr_count($checkoutResponse->getContent(), 'class="input bg-transparent"'));
    }

    public function test_checkout_requires_a_live_seat_hold(): void
    {
        $showtime = $this->makeShowtime();

        $this->get(route('booking.checkout', $showtime))
            ->assertRedirect(route('booking.seats', $showtime));
    }

    public function test_guest_cannot_choose_counter_payment(): void
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
        ])->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_online_payment_confirms_the_booking_after_verification(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 3);
        Event::fake([SeatAvailabilityChanged::class]);

        $this->post(route('booking.select', $showtime), ['seats' => [$seat->id]])
            ->assertRedirect(route('booking.checkout', $showtime));

        $this->post(route('booking.place', $showtime), [
            'customer_name' => 'Aayush Gurung',
            'customer_phone' => '9811111111',
            'payment_method' => 'esewa',
        ])->assertRedirect();

        Event::assertDispatched(SeatAvailabilityChanged::class, fn (SeatAvailabilityChanged $event): bool => (
            $event->showtimeId === $showtime->id
            && $event->seatIds === [$seat->id]
            && $event->status === 'booked'
        ));

        $payment = Payment::sole();
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame(500.0, (float) $payment->amount);
        $this->assertSame(Booking::STATUS_PENDING, $payment->booking->status);
        $this->assertDatabaseHas('booking_seats', [
            'booking_id' => $payment->booking_id,
            'seat_type' => 'normal',
            'price' => 500,
        ]);

        config([
            'services.esewa.product_code' => 'EPAYTEST',
            'services.esewa.secret_key' => 'test-esewa-secret',
        ]);

        $this->get(route('payment.process', $payment))
            ->assertOk()
            ->assertSee('Continue to eSewa sandbox')
            ->assertSee('https://rc-epay.esewa.com.np/api/epay/main/v2/form', false)
            ->assertSee('name="signed_field_names" value="total_amount,transaction_uuid,product_code"', false);

        Http::preventStrayRequests();
        Http::fake([
            'https://rc.esewa.com.np/api/epay/transaction/status/*' => Http::response([
                'product_code' => 'EPAYTEST',
                'transaction_uuid' => $payment->reference,
                'total_amount' => '500.00',
                'status' => 'COMPLETE',
                'ref_id' => 'esewa-test-transaction',
            ]),
        ]);

        $signedFields = ['total_amount', 'transaction_uuid', 'product_code', 'status'];
        $callbackData = [
            'total_amount' => '500.00',
            'transaction_uuid' => $payment->reference,
            'product_code' => 'EPAYTEST',
            'status' => 'COMPLETE',
            'signed_field_names' => implode(',', $signedFields),
        ];
        $callbackData['signature'] = base64_encode(hash_hmac(
            'sha256',
            implode(',', array_map(
                fn (string $field): string => $field.'='.$callbackData[$field],
                $signedFields,
            )),
            'test-esewa-secret',
            true,
        ));
        $invalidCallbackData = $callbackData;
        $invalidCallbackData['signature'] = 'invalid-signature';

        $this->get(route('payment.esewa.success', ['payment' => $payment, 'data' => base64_encode(json_encode($invalidCallbackData, JSON_THROW_ON_ERROR))]))
            ->assertRedirect(route('payment.process', $payment))
            ->assertSessionHasErrors('payment');
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        Http::assertSentCount(0);

        $this->get(route('payment.esewa.success', ['payment' => $payment, 'data' => base64_encode(json_encode($callbackData, JSON_THROW_ON_ERROR))]))
            ->assertRedirect(route('booking.ticket', $payment->booking->booking_code));

        Http::assertSent(fn ($request): bool => (
            str_starts_with($request->url(), 'https://rc.esewa.com.np/api/epay/transaction/status/')
            && $request['product_code'] === 'EPAYTEST'
            && $request['transaction_uuid'] === $payment->reference
        ));

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
        $payment->update(['provider_payload' => ['pidx' => 'test-pidx']]);
        config(['services.khalti.secret_key' => 'test-khalti-secret']);
        Http::preventStrayRequests();
        Http::fake([
            'https://dev.khalti.com/api/v2/epayment/lookup/' => Http::response([
                'pidx' => 'test-pidx',
                'purchase_order_id' => $payment->reference,
                'total_amount' => 50000,
                'status' => 'Completed',
                'transaction_id' => 'khalti-test-transaction',
            ]),
        ]);

        $this->post(route('payment.verify', $payment))->assertRedirect();
        $this->post(route('payment.verify', $payment))->assertRedirect();

        Http::assertSentCount(1);
        $this->assertSame(1, Payment::count());
        $this->assertSame(Payment::STATUS_SUCCESS, $payment->fresh()->status);
        $this->assertSame(Booking::STATUS_CONFIRMED, Booking::sole()->status);
    }

    public function test_khalti_checkout_starts_a_sandbox_payment_session(): void
    {
        config(['services.khalti.secret_key' => 'test-khalti-secret']);
        Http::preventStrayRequests();
        Http::fake([
            'https://dev.khalti.com/api/v2/epayment/initiate/' => Http::response([
                'pidx' => 'test-pidx',
                'payment_url' => 'https://test-pay.khalti.com/?pidx=test-pidx',
            ]),
        ]);
        $showtime = $this->makeShowtime();

        $this->post(route('booking.select', $showtime), ['seats' => [$this->seat($showtime, 1)->id]])
            ->assertRedirect(route('booking.checkout', $showtime));
        $this->post(route('booking.place', $showtime), [
            'customer_name' => 'Khalti Fan',
            'customer_phone' => '9822222222',
            'payment_method' => 'khalti',
        ])->assertRedirect();

        $payment = Payment::sole();
        $this->get(route('payment.process', $payment))
            ->assertOk()
            ->assertSee('Continue to Khalti sandbox')
            ->assertSee('https://test-pay.khalti.com/?pidx=test-pidx', false);

        Http::assertSent(fn ($request): bool => (
            $request->url() === 'https://dev.khalti.com/api/v2/epayment/initiate/'
            && $request->method() === 'POST'
            && $request['amount'] === 50000
            && $request['purchase_order_id'] === $payment->reference
            && $request->hasHeader('Authorization', 'key test-khalti-secret')
        ));
    }

    public function test_khalti_gateway_server_error_is_displayed_without_confirming_the_booking(): void
    {
        config(['services.khalti.secret_key' => 'test-khalti-secret']);
        Http::preventStrayRequests();
        Http::fake([
            'https://dev.khalti.com/api/v2/epayment/initiate/' => Http::response([], 503),
        ]);
        $showtime = $this->makeShowtime();

        $this->post(route('booking.select', $showtime), ['seats' => [$this->seat($showtime, 1)->id]])
            ->assertRedirect(route('booking.checkout', $showtime));
        $this->post(route('booking.place', $showtime), [
            'customer_name' => 'Khalti Fan',
            'customer_phone' => '9822222222',
            'payment_method' => 'khalti',
        ])->assertRedirect();
        $payment = Payment::sole();

        $this->get(route('payment.process', $payment))
            ->assertOk()
            ->assertSee('Khalti could not start this payment. Please try again.');

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(Booking::STATUS_PENDING, $payment->booking->fresh()->status);
    }

    public function test_khalti_connection_failure_is_displayed_without_confirming_the_booking(): void
    {
        config(['services.khalti.secret_key' => 'test-khalti-secret']);
        Http::preventStrayRequests();
        Http::fake([
            'https://dev.khalti.com/api/v2/epayment/initiate/' => Http::failedConnection(),
        ]);
        $showtime = $this->makeShowtime();

        $this->post(route('booking.select', $showtime), ['seats' => [$this->seat($showtime, 2)->id]])
            ->assertRedirect(route('booking.checkout', $showtime));
        $this->post(route('booking.place', $showtime), [
            'customer_name' => 'Khalti Fan',
            'customer_phone' => '9822222222',
            'payment_method' => 'khalti',
        ])->assertRedirect();
        $payment = Payment::sole();

        $this->get(route('payment.process', $payment))
            ->assertOk()
            ->assertSee('Could not connect to Khalti. Please try again.');

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(Booking::STATUS_PENDING, $payment->booking->fresh()->status);
    }

    public function test_khalti_completed_payment_with_wrong_order_details_stays_pending(): void
    {
        config(['services.khalti.secret_key' => 'test-khalti-secret']);
        Http::preventStrayRequests();
        $showtime = $this->makeShowtime();

        $this->post(route('booking.select', $showtime), ['seats' => [$this->seat($showtime, 2)->id]])
            ->assertRedirect(route('booking.checkout', $showtime));
        $this->post(route('booking.place', $showtime), [
            'customer_name' => 'Khalti Fan',
            'customer_phone' => '9822222222',
            'payment_method' => 'khalti',
        ])->assertRedirect();

        $payment = Payment::sole();
        $payment->update(['provider_payload' => ['pidx' => 'test-pidx']]);
        Http::fake([
            'https://dev.khalti.com/api/v2/epayment/lookup/' => Http::response([
                'pidx' => 'test-pidx',
                'purchase_order_id' => 'another-payment',
                'total_amount' => 50000,
                'status' => 'Completed',
            ]),
        ]);

        $this->post(route('payment.verify', $payment))
            ->assertRedirect()
            ->assertSessionHasErrors('payment');

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(Booking::STATUS_PENDING, $payment->booking->fresh()->status);
        $this->assertFalse((bool) $payment->booking->fresh()->is_paid);
    }

    public function test_payment_provider_return_requires_the_booking_session(): void
    {
        $showtime = $this->makeShowtime();
        $this->post(route('booking.select', $showtime), ['seats' => [$this->seat($showtime, 3)->id]])
            ->assertRedirect(route('booking.checkout', $showtime));
        $this->post(route('booking.place', $showtime), [
            'customer_name' => 'Guest Customer',
            'customer_phone' => '9800000000',
            'payment_method' => 'esewa',
        ])->assertRedirect();
        $payment = Payment::sole();

        $this->withSession(['guest_booking_ids' => []])
            ->get(route('payment.esewa.success', $payment))
            ->assertForbidden();

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(Booking::STATUS_PENDING, $payment->booking->fresh()->status);
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
            $this->seat($showtime, 1)->id,
            $this->seat($showtime, 3)->id,
        ]);

        $this->assertSame(1000.0, $totals['subtotal']);
        $this->assertSame(0.0, $totals['discount']);
        $this->assertSame(1000.0, $totals['total']);
    }

    public function test_legacy_seat_tier_uses_normal_pricing_and_label(): void
    {
        $showtime = $this->makeShowtime();
        $seat = $this->seat($showtime, 3);
        $seat->update(['seat_type' => 'premium']);

        $totals = app(BookingService::class)->totals($showtime, [$seat->id]);

        $this->assertSame(500.0, $totals['subtotal']);
        $this->assertSame('normal', $totals['lines'][0]['type']);
        $this->assertSame('Normal', $seat->type_label());
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
        Event::fake([SeatAvailabilityChanged::class]);

        Booking::query()->whereKey($booking->id)->update([
            'created_at' => now()->subMinutes(BookingService::PENDING_TTL_MINUTES + 1),
        ]);

        $service->releaseStaleBookings($showtime);

        $this->assertSame(Booking::STATUS_CANCELLED, $booking->fresh()->status);
        Event::assertDispatched(SeatAvailabilityChanged::class, fn (SeatAvailabilityChanged $event): bool => (
            $event->showtimeId === $showtime->id
            && $event->seatIds === [$seat->id]
            && $event->status === 'available'
        ));

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
                'payment_method' => 'esewa',
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
