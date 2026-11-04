<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    /**
     * The simulated provider checkout page (eSewa / Khalti / card).
     */
    public function process(Request $request, Payment $payment): View|RedirectResponse
    {
        abort_unless($this->owns($request, $payment), 403);

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return redirect()->route('booking.ticket', $payment->booking->booking_code)
                ->with('success', ui('booking_confirmed', 'Booking confirmed!'));
        }

        $payment->load(['booking.movie', 'booking.cinema.city']);

        return view('booking.payment', [
            'payment' => $payment,
            'booking' => $payment->booking,
            'providerLabel' => $payment->provider_label(),
        ]);
    }

    /**
     * Server-side verification of the payment with the provider.
     */
    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($this->owns($request, $payment), 403);

        $payment = $this->paymentService->verify($payment);

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return redirect()->route('booking.ticket', $payment->booking->booking_code)
                ->with('success', ui('booking_confirmed', 'Booking confirmed!'));
        }

        return back()->withErrors([
            'payment' => ui('payment_failed', 'Payment could not be verified. Please try again.'),
        ]);
    }

    /**
     * Payment history for the signed-in customer.
     */
    public function history(Request $request): View
    {
        $payments = Payment::query()
            ->whereHas('booking', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with('booking')
            ->latest()
            ->paginate(15);

        return view('account.payments', compact('payments'));
    }

    private function owns(Request $request, Payment $payment): bool
    {
        if ($request->user() && $payment->booking->user_id === $request->user()->id) {
            return true;
        }

        // Guests keep their bookings in the session they created them from.
        return in_array($payment->booking_id, session('guest_booking_ids', []), true);
    }
}
