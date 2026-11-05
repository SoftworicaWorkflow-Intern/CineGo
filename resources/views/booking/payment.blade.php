@extends('layouts.app')

@section('title', ui('step_payment', 'Payment'))

@section('content')
    <div class="container-page py-8">
        @include('partials.steps', ['current' => 6, 'showtime' => $showtime])

        <div class="mx-auto max-w-2xl">
            <div class="mb-6 text-center">
                <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-cg-red/15 text-cg-red">
                    <x-icon name="wallet" :size="26" />
                </span>
                <h1 class="font-display text-3xl tracking-wide text-cg-white">{{ $providerLabel }}</h1>
                <p class="mt-1.5 text-sm text-cg-muted">
                    {{ ui('payment_redirect', 'Confirm the payment below to complete your booking.') }}
                </p>
            </div>

            <div class="card overflow-hidden">
                {{-- Amount header --}}
                <div class="border-b border-cg-border-soft bg-gradient-to-r from-cg-red/20 to-transparent px-6 py-5">
                    <div class="flex items-end justify-between gap-4">
                        <span>
                            <span class="block text-xs uppercase tracking-wide text-cg-dim">{{ ui('total', 'Total') }}</span>
                            <span class="font-display text-4xl text-cg-white">Rs. {{ number_format($payment->amount, 2) }}</span>
                        </span>
                        <span class="badge-dark font-mono">{{ $payment->reference }}</span>
                    </div>
                </div>

                <div class="space-y-4 px-6 py-6">
                    <dl class="space-y-2.5 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('movies', 'Movie') }}</dt>
                            <dd class="text-right font-medium text-cg-white">{{ $booking->movie_title }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('dates', 'Date & time') }}</dt>
                            <dd class="text-right font-medium text-cg-white">
                                {{ $booking->show_date->format('D, M j, Y') }} · {{ \Illuminate\Support\Str::of($booking->start_time)->substr(0, 5) }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('selected_seats', 'Selected seats') }}</dt>
                            <dd class="text-right font-medium text-cg-white">{{ $booking->seat_labels() }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('booking_id', 'Booking ID') }}</dt>
                            <dd class="text-right font-mono font-medium text-cg-white">{{ $booking->booking_code }}</dd>
                        </div>
                    </dl>

                    @if ($errors->has('payment'))
                        <p class="rounded-lg border border-cg-red/50 bg-cg-red/10 px-4 py-3 text-sm text-cg-white">
                            {{ $errors->first('payment') }}
                        </p>
                    @endif

                    <form action="{{ route('payment.verify', $payment) }}" method="POST" id="pay-form">
                        @csrf
                        <button type="submit" id="pay-button" class="btn-primary btn-lg btn-block">
                            <x-icon name="shield-check" :size="18" />
                            {{ ui('complete_payment', 'Complete payment') }}
                        </button>
                    </form>

                    <p class="flex items-start gap-2 text-center text-xs text-cg-dim sm:text-left">
                        <x-icon name="info" :size="14" class="mt-0.5 shrink-0 text-cg-red" />
                        {{ ui('demo_payment', 'Demo mode: the payment is verified by our server, no real money moves.') }}
                    </p>
                </div>
            </div>

            @if ($showtime)
                <p class="mt-5 text-center text-sm text-cg-muted">
                    <a href="{{ route('booking.seats', $showtime) }}" class="font-medium text-cg-red hover:underline">
                        ← {{ ui('back_to_seats', 'Back to seat selection') }}
                    </a>
                </p>
            @endif
        </div>
    </div>

    <script>
        document.getElementById('pay-form').addEventListener('submit', function () {
            var button = document.getElementById('pay-button');
            button.disabled = true;
            button.classList.add('opacity-70');
            button.textContent = @json(ui('processing', 'Processing payment…'));
        });
    </script>
@endsection
