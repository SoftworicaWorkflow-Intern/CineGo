<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentGatewayException;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    /**
     * Show provider checkout instructions and redirect details.
     */
    public function process(Request $request, Payment $payment): View|RedirectResponse
    {
        abort_unless($this->owns($request, $payment), 403);

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return redirect()->route('booking.ticket', $payment->booking->booking_code)
                ->with('success', ui('booking_confirmed', 'Booking confirmed!'));
        }

        $payment->load(['booking.movie', 'booking.cinema.city']);
        $gatewayUrl = null;
        $formParameters = [];
        $gatewayError = null;

        try {
            if ($payment->provider === 'esewa') {
                $gatewayUrl = (string) config('services.esewa.form_url');
                $formParameters = $this->paymentService->esewaFormParameters($payment);
            } elseif ($payment->provider === 'khalti') {
                $gatewayUrl = $this->paymentService->initiateKhalti($payment);
            } else {
                throw new PaymentGatewayException('This payment provider is not supported.');
            }
        } catch (PaymentGatewayException $exception) {
            Log::warning('Payment checkout could not be prepared.', [
                'payment_id' => $payment->id,
                'provider' => $payment->provider,
                'message' => $exception->getMessage(),
            ]);
            $gatewayError = $exception->getMessage();
        }

        return view('booking.payment', [
            'payment' => $payment,
            'booking' => $payment->booking,
            'showtime' => $payment->booking->showtime,
            'providerLabel' => $payment->provider_label(),
            'gatewayUrl' => $gatewayUrl,
            'formParameters' => $formParameters,
            'gatewayError' => $gatewayError,
        ]);
    }

    /**
     * Server-side verification of the payment with the provider.
     */
    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($this->owns($request, $payment), 403);

        try {
            $payment = $this->paymentService->verify($payment);
        } catch (PaymentGatewayException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return redirect()->route('booking.ticket', $payment->booking->booking_code)
                ->with('success', ui('booking_confirmed', 'Booking confirmed!'));
        }

        return back()->withErrors([
            'payment' => ui('payment_failed', 'Payment could not be verified. Please try again.'),
        ]);
    }

    public function esewaSuccess(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($this->owns($request, $payment), 403);

        $data = $request->query('data');

        return $this->verifyProviderReturn(
            $payment,
            esewaData: is_string($data) ? $data : null,
        );
    }

    public function esewaFailure(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($this->owns($request, $payment), 403);

        return redirect()->route('payment.process', $payment)
            ->withErrors(['payment' => 'The eSewa payment was not completed. You can try again.']);
    }

    public function khaltiReturn(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($this->owns($request, $payment), 403);

        $pidx = $request->query('pidx');

        return $this->verifyProviderReturn(
            $payment,
            pidx: is_string($pidx) ? $pidx : null,
        );
    }

    private function verifyProviderReturn(
        Payment $payment,
        ?string $pidx = null,
        ?string $esewaData = null,
    ): RedirectResponse {
        try {
            $payment = $this->paymentService->verify($payment, $pidx, $esewaData);
        } catch (PaymentGatewayException $exception) {
            return redirect()->route('payment.process', $payment)
                ->withErrors(['payment' => $exception->getMessage()]);
        }

        if ($payment->status === Payment::STATUS_SUCCESS) {
            return redirect()->route('booking.ticket', $payment->booking->booking_code)
                ->with('success', ui('booking_confirmed', 'Booking confirmed!'));
        }

        return redirect()->route('payment.process', $payment)
            ->withErrors([
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
