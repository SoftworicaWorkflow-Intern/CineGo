@extends('layouts.app')

@section('title', ui('step_customer_info', 'Your Details'))

@php
    $discount = 0;
    $seatLabels = $totals['lines'];
@endphp

@section('content')
    <div class="container-page py-8">
        @include('partials.steps', ['current' => 5, 'showtime' => $showtime])

        <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
            <div class="min-w-0">
                <div class="mb-6">
                    <h1 class="font-display text-3xl tracking-wide text-white">
                        {{ ui('step_customer_info', 'Your Details') }}
                    </h1>
                    <p class="mt-1.5 text-sm text-cg-muted">
                        {{ ui('guest_checkout', 'Booking as guest — create an account after to track it.') }}
                        @guest
                            <a href="{{ route('login') }}" class="font-semibold text-cg-red hover:underline">
                                {{ ui('login_here', 'Log in here') }}
                            </a>
                        @endguest
                    </p>
                </div>

                <form action="{{ route('booking.place', $showtime) }}" method="POST" id="checkout-form" class="space-y-6">
                    @csrf

                    {{-- Customer information --}}
                    <section class="card p-5 sm:p-6">
                        <h2 class="mb-4 flex items-center gap-2 font-semibold text-cg-white">
                            <x-icon name="user" :size="17" class="text-cg-red" />
                            {{ ui('customer_info', 'Customer information') }}
                        </h2>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="label" for="customer_name">{{ ui('full_name', 'Full name') }} *</label>
                                <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" class="input" required maxlength="100" autocomplete="name">
                            </div>

                            <div>
                                <label class="label" for="customer_email">{{ ui('email_address', 'Email address') }}</label>
                                <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" class="input" maxlength="160" autocomplete="email" placeholder="you@example.com">
                            </div>

                            <div>
                                <label class="label" for="customer_phone">{{ ui('mobile_number', 'Mobile number') }} *</label>
                                <input id="customer_phone" type="tel" name="customer_phone" value="{{ old('customer_phone', auth()->user()?->phone) }}" class="input" required maxlength="20" autocomplete="tel" placeholder="98XXXXXXXX">
                            </div>
                        </div>
                    </section>

                    {{-- Promo code --}}
                    <section class="card p-5 sm:p-6">
                        <h2 class="mb-4 flex items-center gap-2 font-semibold text-cg-white">
                            <x-icon name="tag" :size="17" class="text-cg-red" />
                            {{ ui('promo_code', 'Promo code') }}
                        </h2>

                        <div class="flex gap-2">
                            <input
                                type="text"
                                id="promo-input"
                                name="promo_code"
                                value="{{ old('promo_code') }}"
                                class="input uppercase tracking-widest"
                                maxlength="40"
                                placeholder="CINEGO10"
                                autocomplete="off"
                            >
                            <button type="button" id="promo-button" class="btn-outline shrink-0 px-5">
                                {{ ui('apply_promo', 'Apply') }}
                            </button>
                        </div>
                        <p id="promo-message" class="mt-2 hidden text-sm"></p>
                        <input type="hidden" name="promo_applied" id="promo-applied" value="">
                    </section>

                    {{-- Payment --}}
                    <section class="card p-5 sm:p-6">
                        <h2 class="mb-4 flex items-center gap-2 font-semibold text-cg-white">
                            <x-icon name="credit-card" :size="17" class="text-cg-red" />
                            {{ ui('payment_method', 'Payment method') }}
                        </h2>

                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($paymentMethods as $method => $label)
                                <label class="cg-pay-option">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="{{ $method }}"
                                        class="peer sr-only"
                                        @checked(old('payment_method', 'esewa') === $method)
                                        required
                                    >
                                    <span class="flex items-center gap-3 rounded-xl border border-cg-border bg-cg-charcoal px-4 py-3.5 transition hover:border-cg-red peer-checked:border-cg-red peer-checked:bg-cg-red/10">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cg-red/15 text-cg-red">
                                            <x-icon name="{{ $method === 'counter' ? 'armchair' : ($method === 'card' ? 'credit-card' : 'wallet') }}" :size="17" />
                                        </span>
                                        <span>
                                            <span class="block text-sm font-semibold text-cg-white">{{ $label }}</span>
                                            <span class="block text-[11px] text-cg-dim">
                                                @if ($method === 'counter')
                                                    {{ ui('pay_at_counter', 'Pay at the counter') }}
                                                @elseif ($method === 'card')
                                                    {{ ui('card_note', 'Visa / Mastercard / debit') }}
                                                @else
                                                    {{ ui('wallet_note', 'Instant wallet payment') }}
                                                @endif
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <p class="mt-4 flex items-start gap-2 rounded-lg border border-cg-border bg-cg-charcoal px-3.5 py-3 text-xs text-cg-muted">
                            <x-icon name="shield-check" :size="15" class="mt-0.5 shrink-0 text-cg-red" />
                            {{ ui('demo_payment', 'Demo mode: the payment is verified by our server, no real money moves.') }}
                        </p>
                    </section>

                    <button type="submit" id="place-button" class="btn-primary btn-lg btn-block">
                        <x-icon name="shield-check" :size="18" />
                        {{ ui('pay_now', 'Pay & confirm') }}
                    </button>
                </form>
            </div>

            {{-- Booking summary --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="card p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <img src="{{ $showtime->movie->poster_url }}" alt="" class="h-20 w-14 rounded-lg object-cover" width="56" height="80">
                        <div class="min-w-0">
                            <p class="font-semibold text-cg-white">{{ $showtime->movie->localised_title() }}</p>
                            <p class="mt-0.5 text-xs text-cg-muted">{{ $showtime->movie->genre_label() }} · {{ $showtime->movie->language }}</p>
                        </div>
                    </div>

                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('cinemas', 'Cinema') }}</dt>
                            <dd class="text-right font-medium text-cg-white">{{ $showtime->cinema->localised_name() }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('screens', 'Screen') }}</dt>
                            <dd class="text-right font-medium text-cg-white">{{ $showtime->hall->name }} · {{ $showtime->format }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('dates', 'Date') }}</dt>
                            <dd class="text-right font-medium text-cg-white">{{ $showtime->show_date->format('D, M j, Y') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('times', 'Time') }}</dt>
                            <dd class="text-right font-medium text-cg-white">{{ $showtime->start_time_label() }}</dd>
                        </div>
                    </dl>

                    <div class="my-4 border-t border-cg-border-soft"></div>

                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-cg-dim">
                        {{ ui('selected_seats', 'Selected seats') }} ({{ count($seatLabels) }})
                    </p>
                    <ul class="mb-4 space-y-1.5">
                        @foreach ($seatLabels as $line)
                            <li class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2 text-cg-white">
                                    <span class="rounded bg-cg-red/15 px-2 py-0.5 text-xs font-bold text-cg-red">{{ $line['label'] }}</span>
                                    <span class="text-xs capitalize text-cg-muted">{{ $line['type'] }}</span>
                                </span>
                                <span class="text-cg-muted">Rs. {{ number_format($line['price']) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="space-y-2 border-t border-cg-border-soft pt-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-cg-muted">{{ ui('subtotal', 'Subtotal') }}</span>
                            <span class="text-cg-white">Rs. <span id="summary-subtotal">{{ number_format($totals['subtotal']) }}</span></span>
                        </div>
                        <div class="flex justify-between" id="discount-row" style="display: none;">
                            <span class="text-cg-green">{{ ui('discount', 'Discount') }} (<span id="discount-code"></span>)</span>
                            <span class="text-cg-green">− Rs. <span id="summary-discount">0</span></span>
                        </div>
                        <div class="flex justify-between border-t border-cg-border-soft pt-3 text-base font-bold">
                            <span class="text-cg-white">{{ ui('total', 'Total') }}</span>
                            <span class="text-cg-red">Rs. <span id="summary-total">{{ number_format($totals['total']) }}</span></span>
                        </div>
                    </div>

                    <p class="mt-4 flex items-start gap-2 text-xs text-cg-dim">
                        <x-icon name="clock" :size="14" class="mt-0.5 shrink-0 text-cg-amber" />
                        {{ ui('hold_notice', 'Your seats are held while you complete this page.') }}
                    </p>
                </div>
            </aside>
        </div>
    </div>

    <script>
        (function () {
            var subtotal = {{ $totals['subtotal'] }};
            var appliedCode = '';
            var appliedDiscount = 0;

            var input = document.getElementById('promo-input');
            var button = document.getElementById('promo-button');
            var message = document.getElementById('promo-message');
            var hidden = document.getElementById('promo-applied');
            var discountRow = document.getElementById('discount-row');
            var discountCode = document.getElementById('discount-code');
            var discountEl = document.getElementById('summary-discount');
            var totalEl = document.getElementById('summary-total');

            function formatAmount(value) {
                return Number(value).toLocaleString('en-US', { maximumFractionDigits: 2 });
            }

            function renderTotals() {
                discountRow.style.display = appliedDiscount > 0 ? 'flex' : 'none';
                discountCode.textContent = appliedCode;
                discountEl.textContent = formatAmount(appliedDiscount);
                totalEl.textContent = formatAmount(Math.max(0, subtotal - appliedDiscount));
            }

            button.addEventListener('click', function () {
                var code = input.value.trim();
                message.classList.add('hidden');

                if (!code) return;

                button.disabled = true;
                button.textContent = '…';

                fetch(@json(route('offers.validate')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ code: code, subtotal: subtotal })
                })
                    .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
                    .then(function (result) {
                        message.classList.remove('hidden');

                        if (result.ok && result.data.valid) {
                            appliedCode = result.data.code;
                            appliedDiscount = Number(result.data.discount);
                            hidden.value = appliedCode;
                            message.className = 'mt-2 text-sm text-cg-green';
                            message.textContent = result.data.message;
                        } else {
                            appliedCode = '';
                            appliedDiscount = 0;
                            hidden.value = '';
                            message.className = 'mt-2 text-sm text-cg-red';
                            message.textContent = result.data.message || @json(ui('promo_invalid', 'That promo code is not valid.'));
                        }

                        renderTotals();
                    })
                    .catch(function () {
                        message.classList.remove('hidden');
                        message.className = 'mt-2 text-sm text-cg-red';
                        message.textContent = @json(ui('try_again', 'Please try again.'));
                    })
                    .finally(function () {
                        button.disabled = false;
                        button.textContent = @json(ui('apply_promo', 'Apply'));
                    });
            });

            document.getElementById('checkout-form').addEventListener('submit', function () {
                var placeButton = document.getElementById('place-button');
                placeButton.disabled = true;
                placeButton.classList.add('opacity-70');
            });
        })();
    </script>
@endsection
