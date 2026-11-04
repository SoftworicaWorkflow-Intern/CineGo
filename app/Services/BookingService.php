<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Offer;
use App\Models\Seat;
use App\Models\SeatLock;
use App\Models\Showtime;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Minutes a pending (unpaid) booking keeps holding its seats.
     */
    public const PENDING_TTL_MINUTES = 15;

    public const MAX_SEATS_PER_BOOKING = 10;

    /**
     * Seat ids for a showtime, split by state.
     *
     * @return array{booked: list<int>, locked: list<int>}
     */
    public function seatStatuses(Showtime $showtime, ?string $holder = null): array
    {
        $this->releaseStaleBookings($showtime);
        SeatLock::purgeExpired();

        $seatIds = Seat::query()
            ->where('hall_id', $showtime->hall_id)
            ->where('is_active', true)
            ->pluck('id');

        $booked = BookingSeat::query()
            ->whereIn('seat_id', $seatIds)
            ->whereHas('booking', function ($query) use ($showtime) {
                $query->where('showtime_id', $showtime->id)
                    ->where('status', '!=', Booking::STATUS_CANCELLED);
            })
            ->pluck('seat_id')
            ->all();

        $lockedQuery = SeatLock::query()
            ->live()
            ->where('showtime_id', $showtime->id)
            ->whereIn('seat_id', $seatIds);

        if ($holder !== null) {
            $lockedQuery->where('holder', '!=', $holder);
        }

        return [
            'booked' => array_map('intval', $booked),
            'locked' => array_map('intval', $lockedQuery->pluck('seat_id')->all()),
        ];
    }

    /**
     * Server-side price calculation for a set of seats.
     *
     * @param  list<int>  $seatIds
     * @return array{subtotal: float, discount: float, total: float, lines: list<array{seat_id: int, label: string, type: string, price: float}>}
     */
    public function totals(Showtime $showtime, array $seatIds, ?Offer $offer = null): array
    {
        $seats = Seat::query()
            ->whereIn('id', $seatIds)
            ->where('hall_id', $showtime->hall_id)
            ->where('is_active', true)
            ->get();

        $lines = $seats->map(fn (Seat $seat) => [
            'seat_id' => $seat->id,
            'label' => $seat->label(),
            'type' => $seat->seat_type,
            'price' => $showtime->price_for($seat->seat_type),
        ])->values()->all();

        $subtotal = array_sum(array_column($lines, 'price'));
        $discount = $offer ? $offer->discount_for($subtotal) : 0.0;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total' => round(max(0, $subtotal - $discount), 2),
            'lines' => $lines,
        ];
    }

    /**
     * Create the booking, atomically verifying that every seat is still free.
     *
     * @param  list<int>  $seatIds
     * @param  array{customer_name: string, customer_email?: ?string, customer_phone: string}  $customer
     *
     * @throws ValidationException
     */
    public function createBooking(
        Showtime $showtime,
        array $seatIds,
        array $customer,
        ?Offer $offer = null,
        ?string $holder = null,
        string $status = Booking::STATUS_PENDING,
    ): Booking {
        $seatIds = array_values(array_unique(array_map('intval', $seatIds)));

        if ($seatIds === []) {
            throw ValidationException::withMessages([
                'seats' => ui('no_seats_selected', 'Select at least one seat to continue.'),
            ]);
        }

        if (count($seatIds) > self::MAX_SEATS_PER_BOOKING) {
            throw ValidationException::withMessages([
                'seats' => ui('max_seats', 'You can book up to 10 seats per booking.'),
            ]);
        }

        return DB::transaction(function () use ($showtime, $seatIds, $customer, $offer, $holder, $status) {
            $this->releaseStaleBookings($showtime);
            SeatLock::purgeExpired();

            $seats = Seat::query()
                ->where('hall_id', $showtime->hall_id)
                ->where('is_active', true)
                ->whereIn('id', $seatIds)
                ->get();

            if ($seats->count() !== count($seatIds)) {
                throw ValidationException::withMessages([
                    'seats' => ui('seats_invalid', 'One or more selected seats are not valid.'),
                ]);
            }

            $alreadyBooked = BookingSeat::query()
                ->whereIn('seat_id', $seatIds)
                ->whereHas('booking', function ($query) use ($showtime) {
                    $query->where('showtime_id', $showtime->id)
                        ->where('status', '!=', Booking::STATUS_CANCELLED);
                })
                ->exists();

            if ($alreadyBooked) {
                throw ValidationException::withMessages([
                    'seats' => ui('seats_taken', 'One of your seats was just booked by someone else. Please pick again.'),
                ]);
            }

            $lockedByOther = SeatLock::query()
                ->live()
                ->where('showtime_id', $showtime->id)
                ->whereIn('seat_id', $seatIds)
                ->when($holder !== null, fn ($query) => $query->where('holder', '!=', $holder))
                ->exists();

            if ($lockedByOther) {
                throw ValidationException::withMessages([
                    'seats' => ui('seats_locked', 'One of your seats is temporarily held by another customer.'),
                ]);
            }

            $totals = $this->totals($showtime, $seatIds, $offer);

            $booking = Booking::create([
                'booking_code' => Booking::generateCode(),
                'user_id' => auth()->id(),
                'movie_id' => $showtime->movie_id,
                'cinema_id' => $showtime->cinema_id,
                'hall_id' => $showtime->hall_id,
                'showtime_id' => $showtime->id,
                'show_date' => $showtime->show_date->toDateString(),
                'start_time' => $showtime->start_time,
                'movie_title' => $showtime->movie->title,
                'cinema_name' => $showtime->cinema->name,
                'hall_name' => $showtime->hall->name,
                'format' => $showtime->format,
                'customer_name' => $customer['customer_name'],
                'customer_email' => $customer['customer_email'] ?? null,
                'customer_phone' => $customer['customer_phone'],
                'seat_count' => count($seatIds),
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount'],
                'total_amount' => $totals['total'],
                'offer_code' => $offer?->code,
                'status' => $status,
                'is_paid' => $status === Booking::STATUS_CONFIRMED,
            ]);

            BookingSeat::insert(array_map(fn (array $line) => [
                'booking_id' => $booking->id,
                'seat_id' => $line['seat_id'],
                'seat_label' => $line['label'],
                'seat_type' => $line['type'],
                'price' => $line['price'],
                'created_at' => now(),
                'updated_at' => now(),
            ], $totals['lines']));

            SeatLock::query()
                ->where('showtime_id', $showtime->id)
                ->whereIn('seat_id', $seatIds)
                ->delete();

            return $booking->load('seats', 'movie', 'cinema', 'hall');
        });
    }

    /**
     * Expire pending bookings whose payment window has closed, freeing seats.
     */
    public function releaseStaleBookings(Showtime $showtime): void
    {
        Booking::query()
            ->where('showtime_id', $showtime->id)
            ->where('status', Booking::STATUS_PENDING)
            ->where('created_at', '<=', now()->subMinutes(self::PENDING_TTL_MINUTES))
            ->update(['status' => Booking::STATUS_CANCELLED]);
    }

    /**
     * Hold seats for the checkout window. Returns seats that could not be held.
     *
     * @param  list<int>  $seatIds
     * @return list<int>
     */
    public function lockSeats(Showtime $showtime, array $seatIds, string $holder): array
    {
        SeatLock::purgeExpired();
        $this->releaseStaleBookings($showtime);

        $held = [];

        foreach ($seatIds as $seatId) {
            $seatId = (int) $seatId;

            $booked = BookingSeat::query()
                ->where('seat_id', $seatId)
                ->whereHas('booking', function ($query) use ($showtime) {
                    $query->where('showtime_id', $showtime->id)
                        ->where('status', '!=', Booking::STATUS_CANCELLED);
                })
                ->exists();

            if ($booked) {
                $held[] = $seatId;

                continue;
            }

            SeatLock::updateOrInsert(
                ['showtime_id' => $showtime->id, 'seat_id' => $seatId],
                ['holder' => $holder, 'expires_at' => now()->addMinutes(SeatLock::TTL_MINUTES), 'created_at' => now(), 'updated_at' => now()],
            );
        }

        return $held;
    }
}
