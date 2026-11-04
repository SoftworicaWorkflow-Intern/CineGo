@extends('layouts.admin')

@section('title', ui('booking_details', 'Booking details'))

@php
    $lastPayment = $booking->payments->first();
@endphp

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.bookings.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> {{ ui('back', 'Back') }}
        </a>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('booking.verify', $booking->booking_code) }}" target="_blank" rel="noopener" class="btn-outline px-4 py-2 text-sm">
                <x-icon name="qr-code" :size="15" /> {{ ui('verify_ticket', 'Verify ticket') }}
            </a>
            <a href="{{ route('booking.download', $booking->booking_code) }}" class="btn-outline px-4 py-2 text-sm">
                <x-icon name="download" :size="15" /> {{ ui('download_ticket', 'Download ticket') }}
            </a>
            @if (! $booking->checked_in_at && $booking->status === \App\Models\Booking::STATUS_CONFIRMED)
                <form action="{{ route('admin.bookings.checkin', $booking) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary px-4 py-2 text-sm">
                        <x-icon name="check-circle" :size="15" /> {{ ui('mark_used', 'Mark as used') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            {{-- Ticket --}}
            <section class="card p-5 sm:p-6">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-display text-2xl tracking-wide text-cg-white">{{ $booking->movie_title }}</p>
                        <p class="mt-0.5 text-sm text-cg-muted">
                            {{ $booking->cinema_name }} · {{ $booking->hall_name }} · {{ $booking->format }}
                        </p>
                    </div>
                    <span class="badge-dark font-mono text-sm">{{ $booking->booking_code }}</span>
                </div>

                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('date', 'Date') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ $booking->show_date->format('D, M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('times', 'Time') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ \Illuminate\Support\Str::of($booking->start_time)->substr(0, 5) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('seats', 'Seats') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-red">{{ $booking->seat_labels() }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('total', 'Total') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">Rs. {{ number_format($booking->total_amount) }}</dd>
                    </div>
                </dl>

                <div class="mt-4 flex flex-wrap gap-2 border-t border-cg-border-soft pt-4">
                    <span class="{{ $booking->status === \App\Models\Booking::STATUS_CONFIRMED ? 'badge-green' : ($booking->status === \App\Models\Booking::STATUS_CANCELLED ? 'badge-red' : 'badge-amber') }}">
                        {{ $booking->status_label() }}
                    </span>
                    <span class="{{ $booking->is_paid ? 'badge-green' : 'badge-amber' }}">
                        {{ $booking->is_paid ? ui('paid', 'Paid') : ui('unpaid', 'Unpaid') }}
                    </span>
                    @if ($booking->checked_in_at)
                        <span class="badge-dark">{{ ui('used', 'Used') }} · {{ $booking->checked_in_at->format('M j, H:i') }}</span>
                    @endif
                    @if ($booking->offer_code)
                        <span class="badge-dark">{{ ui('promo_code', 'Promo') }}: {{ $booking->offer_code }}</span>
                    @endif
                </div>
            </section>

            {{-- Customer --}}
            <section class="card p-5 sm:p-6">
                <h2 class="mb-4 font-semibold text-cg-white">{{ ui('customer_info', 'Customer') }}</h2>
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('full_name', 'Full name') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ $booking->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('mobile_number', 'Mobile') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ $booking->customer_phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('email_address', 'Email') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ $booking->customer_email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('account', 'Account') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">
                            @if ($booking->user)
                                {{ $booking->user->name }} · {{ $booking->user->email }}
                            @else
                                {{ ui('guest', 'Guest checkout') }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Audit trail --}}
            <section class="card p-5 sm:p-6">
                <h2 class="mb-4 font-semibold text-cg-white">{{ ui('payment_audit', 'Payment audit trail') }}</h2>

                @if ($booking->payments->isEmpty())
                    <p class="text-sm text-cg-muted">{{ ui('no_payments', 'No payments yet.') }}</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($booking->payments as $payment)
                            <li class="rounded-xl border border-cg-border bg-cg-charcoal p-3.5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-cg-white">
                                        {{ $payment->provider_label() }} · Rs. {{ number_format($payment->amount, 2) }}
                                    </span>
                                    <span class="{{ $payment->status === \App\Models\Payment::STATUS_SUCCESS ? 'badge-green' : ($payment->status === \App\Models\Payment::STATUS_FAILED ? 'badge-red' : 'badge-amber') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </div>
                                <p class="mt-1.5 font-mono text-[11px] text-cg-dim">
                                    {{ $payment->reference }} · {{ $payment->created_at->format('M j, Y H:i:s') }}
                                    @if ($payment->verified_at) · verified {{ $payment->verified_at->format('M j, H:i:s') }} @endif
                                </p>
                                @if ($payment->provider_payload)
                                    <details class="mt-2">
                                        <summary class="cursor-pointer text-[11px] text-cg-red">{{ ui('payload', 'Payload') }}</summary>
                                        <pre class="mt-1.5 overflow-x-auto rounded bg-cg-charcoal p-2 text-[10px] text-cg-muted">{{ json_encode($payment->provider_payload, JSON_PRETTY_PRINT) }}</pre>
                                    </details>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            {{-- Staff actions --}}
            <section class="card p-5">
                <h2 class="mb-4 font-semibold text-cg-white">{{ ui('staff_actions', 'Staff actions') }}</h2>

                <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="label" for="status">{{ ui('status', 'Status') }}</label>
                        <select id="status" name="status" class="input cursor-pointer">
                            @foreach ([\App\Models\Booking::STATUS_PENDING => ui('pending', 'Pending'), \App\Models\Booking::STATUS_CONFIRMED => ui('confirmed', 'Confirmed'), \App\Models\Booking::STATUS_CANCELLED => ui('cancelled', 'Cancelled')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $booking->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-cg-white">
                        <input type="checkbox" name="mark_paid" value="1" class="h-4 w-4 rounded accent-cg-red"
                            @checked(old('mark_paid', $booking->is_paid))>
                        {{ ui('mark_paid', 'Mark as paid') }}
                    </label>

                    <p class="flex items-start gap-2 text-[11px] text-cg-dim">
                        <x-icon name="info" :size="13" class="mt-0.5 shrink-0 text-cg-red" />
                        {{ ui('staff_action_note', 'Cancelling frees the seats for other customers.') }}
                    </p>

                    <button type="submit" class="btn-primary w-full justify-center">
                        <x-icon name="check" :size="16" /> {{ ui('save', 'Save changes') }}
                    </button>
                </form>
            </section>

            {{-- Seat price breakdown --}}
            <section class="card p-5">
                <h2 class="mb-3 font-semibold text-cg-white">{{ ui('price_breakdown', 'Price breakdown') }}</h2>
                <ul class="space-y-1.5 text-sm">
                    @foreach ($booking->seats as $seat)
                        <li class="flex items-center justify-between gap-2">
                            <span class="text-cg-white">
                                {{ $seat->seat?->row_label }}{{ $seat->seat?->seat_number }}
                                <span class="text-xs capitalize text-cg-dim">({{ $seat->seat?->seat_type }})</span>
                            </span>
                            <span class="text-cg-muted">Rs. {{ number_format($seat->price) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-3 flex items-center justify-between border-t border-cg-border-soft pt-3 font-bold">
                    <span class="text-cg-white">{{ ui('total', 'Total') }}</span>
                    <span class="text-cg-red">Rs. {{ number_format($booking->total_amount) }}</span>
                </div>
            </section>

            @if ($booking->checked_in_at)
                <section class="card p-5">
                    <h2 class="mb-2 font-semibold text-cg-white">{{ ui('check_in', 'Check-in') }}</h2>
                    <p class="text-sm text-cg-green">
                        ✓ {{ ui('checked_in_at', 'Checked in') }} {{ $booking->checked_in_at->format('M j, Y H:i:s') }}
                    </p>
                </section>
            @endif
        </aside>
    </div>
@endsection
