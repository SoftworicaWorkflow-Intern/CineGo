<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Support\Facades\Notification;

class PaymentService
{
    /**
     * Create a pending payment for a booking. The amount always comes from
     * the server-side booking total, never from the client.
     */
    public function initiate(Booking $booking, string $provider): Payment
    {
        return Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'provider' => $provider,
            'reference' => Payment::generateReference(),
            'amount' => $booking->total_amount,
            'currency' => 'NPR',
            'status' => Payment::STATUS_PENDING,
        ]);
    }

    /**
     * Verify the payment with the provider and settle the booking.
     *
     * In demo mode the provider response is simulated locally, but every
     * check (amount, idempotency, booking state) happens on the server —
     * exactly where a real gateway callback would be validated.
     */
    public function verify(Payment $payment): Payment
    {
        // Idempotent: a settled payment is never processed twice.
        if ($payment->status === Payment::STATUS_SUCCESS) {
            return $payment->refresh();
        }

        $booking = $payment->booking;

        $amountMatches = abs($payment->amount - $booking->total_amount) < 0.01;
        $bookingIsOpen = $booking->status !== Booking::STATUS_CANCELLED;

        $providerResponse = [
            'mode' => 'demo',
            'provider_txn_id' => 'TXN'.strtoupper(substr(bin2hex(random_bytes(8)), 0, 12)),
            'reference' => $payment->reference,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'received_at' => now()->toIso8601String(),
        ];

        if (! $amountMatches || ! $bookingIsOpen) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'provider_payload' => array_merge($providerResponse, [
                    'result' => 'FAILED',
                    'reason' => $amountMatches ? 'booking_closed' : 'amount_mismatch',
                ]),
                'verified_at' => now(),
            ]);

            return $payment->refresh();
        }

        $payment->update([
            'status' => Payment::STATUS_SUCCESS,
            'provider_payload' => array_merge($providerResponse, ['result' => 'SUCCESS']),
            'verified_at' => now(),
        ]);

        $booking->update([
            'status' => Booking::STATUS_CONFIRMED,
            'is_paid' => true,
        ]);

        $this->notify($booking);

        return $payment->refresh();
    }

    /**
     * Confirm a pay-at-counter booking (no online transaction involved).
     */
    public function confirmCounterBooking(Booking $booking): void
    {
        $booking->update([
            'status' => Booking::STATUS_CONFIRMED,
            'is_paid' => false,
        ]);

        $this->notify($booking);
    }

    /**
     * Email + in-app confirmation for registered customers (queued).
     */
    public function notify(Booking $booking): void
    {
        if ($booking->user) {
            Notification::send($booking->user, new BookingConfirmedNotification($booking));
        }
    }
}
