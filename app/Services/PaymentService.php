<?php

namespace App\Services;

use App\Exceptions\PaymentGatewayException;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
     * @return array<string, string>
     */
    public function esewaFormParameters(Payment $payment): array
    {
        $productCode = (string) config('services.esewa.product_code');
        $secret = (string) config('services.esewa.secret_key');

        if ($productCode === '' || $secret === '') {
            throw new PaymentGatewayException('eSewa sandbox credentials are not configured.');
        }

        $this->secureEndpoint((string) config('services.esewa.form_url'), 'eSewa');

        $amount = number_format($payment->amount, 2, '.', '');
        $transactionUuid = $payment->reference;
        $signatureMessage = "total_amount={$amount},transaction_uuid={$transactionUuid},product_code={$productCode}";

        return [
            'amount' => $amount,
            'tax_amount' => '0',
            'total_amount' => $amount,
            'transaction_uuid' => $transactionUuid,
            'product_code' => $productCode,
            'product_service_charge' => '0',
            'product_delivery_charge' => '0',
            'success_url' => route('payment.esewa.success', $payment),
            'failure_url' => route('payment.esewa.failure', $payment),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'signature' => base64_encode(hash_hmac(
                'sha256',
                $signatureMessage,
                $secret,
                true,
            )),
        ];
    }

    public function initiateKhalti(Payment $payment): string
    {
        $secret = (string) config('services.khalti.secret_key');

        if ($secret === '') {
            throw new PaymentGatewayException('Khalti sandbox credentials are not configured.');
        }

        $payload = $payment->provider_payload ?? [];

        if (! empty($payload['pidx']) && ! empty($payload['payment_url'])) {
            return $payload['payment_url'];
        }

        $booking = $payment->booking;
        $baseUrl = $this->secureEndpoint((string) config('services.khalti.api_url'), 'Khalti');

        try {
            $response = Http::acceptJson()
                ->connectTimeout(3)
                ->withHeaders(['Authorization' => 'key '.$secret])
                ->timeout(15)
                ->post($baseUrl.'/epayment/initiate/', [
                    'return_url' => route('payment.khalti.return', $payment),
                    'website_url' => rtrim((string) config('app.url'), '/'),
                    'amount' => (int) round($payment->amount * 100),
                    'purchase_order_id' => $payment->reference,
                    'purchase_order_name' => 'CineGo ticket: '.$booking->movie_title,
                    'customer_info' => [
                        'name' => $booking->customer_name,
                        'email' => $booking->customer_email,
                        'phone' => $booking->customer_phone,
                    ],
                ]);
        } catch (ConnectionException $exception) {
            Log::error('Khalti sandbox initiation request failed.', [
                'payment_id' => $payment->id,
                'exception' => $exception->getMessage(),
            ]);

            throw new PaymentGatewayException('Could not connect to Khalti. Please try again.', previous: $exception);
        }

        if (! $response->successful()) {
            Log::error('Khalti sandbox initiation returned an error.', [
                'payment_id' => $payment->id,
                'status' => $response->status(),
            ]);

            throw new PaymentGatewayException('Khalti could not start this payment. Please try again.');
        }

        $responseData = $response->json();
        $pidx = is_array($responseData) ? ($responseData['pidx'] ?? null) : null;
        $paymentUrl = is_array($responseData) ? ($responseData['payment_url'] ?? null) : null;
        $paymentUrlHost = is_string($paymentUrl) ? parse_url($paymentUrl, PHP_URL_HOST) : null;

        if (! is_string($pidx) || $pidx === '' || ! is_string($paymentUrl)
            || filter_var($paymentUrl, FILTER_VALIDATE_URL) === false
            || parse_url($paymentUrl, PHP_URL_SCHEME) !== 'https'
            || ! is_string($paymentUrlHost)
            || ($paymentUrlHost !== 'khalti.com' && ! str_ends_with($paymentUrlHost, '.khalti.com'))) {
            throw new PaymentGatewayException('Khalti returned an invalid payment session.');
        }

        $payment->update([
            'provider_payload' => array_merge($payload, [
                'pidx' => $pidx,
                'payment_url' => $paymentUrl,
            ]),
        ]);

        return $paymentUrl;
    }

    public function verify(Payment $payment, ?string $pidx = null, ?string $esewaData = null): Payment
    {
        if ($payment->status === Payment::STATUS_SUCCESS) {
            return $payment->refresh();
        }

        $verified = match ($payment->provider) {
            'esewa' => $this->verifyEsewa($payment, $esewaData),
            'khalti' => $this->verifyKhalti($payment, $pidx),
            default => throw new PaymentGatewayException('This payment provider is not supported.'),
        };

        if (! $verified) {
            return $payment->refresh();
        }

        return $this->settle($payment, $verified);
    }

    /**
     * @param  array<string, mixed>  $providerResponse
     */
    private function settle(Payment $payment, array $providerResponse): Payment
    {
        $result = DB::transaction(function () use ($payment, $providerResponse): array {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status === Payment::STATUS_SUCCESS) {
                return ['booking' => $lockedPayment->booking, 'notify' => false];
            }

            $booking = Booking::query()->lockForUpdate()->findOrFail($lockedPayment->booking_id);
            $amountMatches = abs($lockedPayment->amount - $booking->total_amount) < 0.01;
            $bookingIsOpen = $booking->status !== Booking::STATUS_CANCELLED;

            if (! $amountMatches || ! $bookingIsOpen) {
                throw new PaymentGatewayException('The payment amount or booking status did not match.');
            }

            $lockedPayment->update([
                'status' => Payment::STATUS_SUCCESS,
                'provider_payload' => array_merge($lockedPayment->provider_payload ?? [], $providerResponse),
                'verified_at' => now(),
            ]);
            $booking->update([
                'status' => Booking::STATUS_CONFIRMED,
                'is_paid' => true,
            ]);

            return ['booking' => $booking, 'notify' => true];
        });

        if ($result['notify']) {
            $this->notify($result['booking']);
        }

        return $payment->refresh();
    }

    /**
     * @return array<string, mixed>|false
     */
    private function verifyKhalti(Payment $payment, ?string $pidx): array|false
    {
        $secret = (string) config('services.khalti.secret_key');
        $payload = $payment->provider_payload ?? [];
        $expectedPidx = $payload['pidx'] ?? null;

        if ($secret === '' || ! is_string($expectedPidx) || $expectedPidx === '') {
            throw new PaymentGatewayException('Khalti payment verification is not configured.');
        }

        if ($pidx !== null && ! hash_equals($expectedPidx, $pidx)) {
            return false;
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout(3)
                ->withHeaders(['Authorization' => 'key '.$secret])
                ->timeout(15)
                ->post($this->secureEndpoint((string) config('services.khalti.api_url'), 'Khalti').'/epayment/lookup/', [
                    'pidx' => $expectedPidx,
                ]);
        } catch (ConnectionException $exception) {
            Log::error('Khalti sandbox lookup request failed.', [
                'payment_id' => $payment->id,
                'exception' => $exception->getMessage(),
            ]);

            throw new PaymentGatewayException('Could not verify the payment with Khalti. Please try again.', previous: $exception);
        }

        $data = $response->json();

        if (! $response->successful() || ! is_array($data)) {
            throw new PaymentGatewayException('Khalti could not verify this payment. Please try again.');
        }

        if (($data['status'] ?? null) !== 'Completed') {
            return false;
        }

        if (($data['pidx'] ?? null) !== $expectedPidx
            || ($data['purchase_order_id'] ?? null) !== $payment->reference
            || (int) ($data['total_amount'] ?? -1) !== (int) round($payment->amount * 100)) {
            throw new PaymentGatewayException('Khalti payment details did not match this booking.');
        }

        return [
            'mode' => 'sandbox',
            'pidx' => $expectedPidx,
            'status' => $data['status'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'total_amount' => $data['total_amount'],
        ];
    }

    /**
     * @return array<string, mixed>|false
     */
    private function verifyEsewa(Payment $payment, ?string $encodedData): array|false
    {
        if ($encodedData !== null && ! $this->validEsewaCallback($payment, $encodedData)) {
            return false;
        }

        $productCode = (string) config('services.esewa.product_code');
        $baseUrl = $this->secureEndpoint((string) config('services.esewa.status_url'), 'eSewa');

        try {
            $response = Http::acceptJson()
                ->connectTimeout(3)
                ->timeout(15)
                ->get($baseUrl.'/', [
                    'product_code' => $productCode,
                    'total_amount' => number_format($payment->amount, 2, '.', ''),
                    'transaction_uuid' => $payment->reference,
                ]);
        } catch (ConnectionException $exception) {
            Log::error('eSewa sandbox status request failed.', [
                'payment_id' => $payment->id,
                'exception' => $exception->getMessage(),
            ]);

            throw new PaymentGatewayException('Could not verify the payment with eSewa. Please try again.', previous: $exception);
        }

        $data = $response->json();

        if (! $response->successful() || ! is_array($data)) {
            throw new PaymentGatewayException('eSewa could not verify this payment. Please try again.');
        }

        if (($data['status'] ?? null) !== 'COMPLETE') {
            return false;
        }

        if (($data['transaction_uuid'] ?? null) !== $payment->reference
            || ($data['product_code'] ?? null) !== $productCode
            || abs((float) str_replace(',', '', (string) ($data['total_amount'] ?? '0')) - $payment->amount) >= 0.01) {
            throw new PaymentGatewayException('eSewa payment details did not match this booking.');
        }

        return [
            'mode' => 'sandbox',
            'transaction_uuid' => $payment->reference,
            'status' => $data['status'],
            'ref_id' => $data['ref_id'] ?? null,
            'total_amount' => $data['total_amount'],
        ];
    }

    private function validEsewaCallback(Payment $payment, string $encodedData): bool
    {
        $secret = (string) config('services.esewa.secret_key');
        $decoded = base64_decode($encodedData, true);
        $data = is_string($decoded) ? json_decode($decoded, true) : null;

        if ($secret === '' || ! is_array($data)
            || ($data['transaction_uuid'] ?? null) !== $payment->reference
            || ($data['product_code'] ?? null) !== config('services.esewa.product_code')
            || ($data['status'] ?? null) !== 'COMPLETE'
            || abs((float) str_replace(',', '', (string) ($data['total_amount'] ?? '0')) - $payment->amount) >= 0.01) {
            return false;
        }

        $signedFields = explode(',', (string) ($data['signed_field_names'] ?? ''));
        $signature = $data['signature'] ?? null;

        if ($signedFields === [] || ! is_string($signature) || $signature === ''
            || array_diff(['total_amount', 'transaction_uuid', 'product_code', 'status'], $signedFields) !== []) {
            return false;
        }

        foreach ($signedFields as $field) {
            if (! array_key_exists($field, $data) || ! is_scalar($data[$field]) || $field === 'signature') {
                return false;
            }
        }

        $message = implode(',', array_map(
            fn (string $field): string => $field.'='.(string) $data[$field],
            $signedFields,
        ));
        $expectedSignature = base64_encode(hash_hmac('sha256', $message, $secret, true));

        return hash_equals($expectedSignature, $signature);
    }

    private function secureEndpoint(string $url, string $provider): string
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new PaymentGatewayException($provider.' sandbox endpoint must be a valid HTTPS URL.');
        }

        return rtrim($url, '/');
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
