@extends('layouts.app')

@section('title', ui('step_confirmation', 'Confirmation').' · '.$booking->booking_code)

@php
    $isCheckedIn = $booking->checked_in_at !== null;
    $isCancelled = $booking->status === \App\Models\Booking::STATUS_CANCELLED;
    $paid = $booking->is_paid;
@endphp

@section('content')
    <div class="container-page py-8">
        @include('partials.steps', ['current' => 7, 'showtime' => $booking->showtime])

        <div class="mx-auto max-w-3xl">
            {{-- Confirmation header --}}
            <div class="mb-7 text-center">
                <span class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full
                    {{ $isCancelled ? 'bg-cg-red/15 text-cg-red' : 'bg-cg-green/15 text-cg-green' }}">
                    <x-icon name="{{ $isCancelled ? 'x' : 'check-circle' }}" :size="32" />
                </span>

                <h1 class="font-display text-4xl tracking-wide text-cg-white">
                    {{ $isCancelled ? ui('booking_cancelled', 'Booking cancelled') : ui('booking_confirmed', 'Booking confirmed!') }}
                </h1>
                <p class="mt-2 text-cg-muted">
                    {{ $isCancelled
                        ? ui('booking_cancelled_text', 'This booking has been cancelled.')
                        : ui('booking_confirmed_text', 'Your seats are reserved. Show this ticket at the entrance.') }}
                </p>
            </div>

            {{-- Digital ticket --}}
            <div class="cg-ticket relative overflow-hidden border border-cg-border shadow-2xl shadow-black/50">
                {{-- Perforation notches --}}
                <span class="cg-ticket-notch top-0 -translate-y-1/2"></span>
                <span class="cg-ticket-notch bottom-0 translate-y-1/2"></span>
                <span class="cg-ticket-notch top-1/2 right-0 left-auto translate-x-1/2"></span>
                <span class="cg-ticket-notch top-1/2 left-0 -translate-x-1/2"></span>

                {{-- Movie header --}}
                <div class="relative border-b border-dashed border-cg-border px-5 pt-6 pb-5 sm:px-8">
                    <div class="absolute inset-0 bg-gradient-to-r from-cg-red/20 to-transparent"></div>
                    <div class="relative flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-cg-red">CineGo</p>
                            <h2 class="mt-1 font-display text-3xl leading-tight tracking-wide text-cg-white">
                                {{ $booking->movie_title }}
                            </h2>
                            <p class="mt-1 text-sm text-cg-muted">{{ $booking->cinema_name }} · {{ $booking->hall_name }} · {{ $booking->format }}</p>
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <span class="badge-dark font-mono text-sm">{{ $booking->booking_code }}</span>
                            <span class="{{ $isCancelled ? 'badge-red' : ($paid ? 'badge-green' : 'badge-amber') }}">
                                {{ $isCancelled
                                    ? ui('cancelled', 'Cancelled')
                                    : ($paid ? ui('paid', 'Paid') : ui('unpaid', 'Pay at counter')) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Details grid --}}
                <div class="grid grid-cols-2 gap-x-6 gap-y-5 border-b border-dashed border-cg-border px-5 py-6 sm:grid-cols-4 sm:px-8">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('dates', 'Date') }}</p>
                        <p class="mt-1 text-sm font-semibold text-cg-white">{{ $booking->show_date->format('D, M j, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('times', 'Time') }}</p>
                        <p class="mt-1 text-sm font-semibold text-cg-white">{{ \Illuminate\Support\Str::of($booking->start_time)->substr(0, 5) }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('seats', 'Seats') }}</p>
                        <p class="mt-1 text-sm font-semibold text-cg-white">{{ $booking->seat_labels() }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('total', 'Total') }}</p>
                        <p class="mt-1 text-sm font-semibold text-cg-white">Rs. {{ number_format($booking->total_amount) }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('customer_info', 'Customer') }}</p>
                        <p class="mt-1 text-sm font-semibold text-cg-white">{{ $booking->customer_name }}</p>
                        <p class="text-xs text-cg-muted">{{ $booking->customer_phone }}@if($booking->customer_email) · {{ $booking->customer_email }}@endif</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('payment_status', 'Payment status') }}</p>
                        <p class="mt-1 text-sm font-semibold {{ $paid ? 'text-cg-green' : 'text-cg-amber' }}">
                            {{ $paid ? ui('paid', 'Paid') : ui('unpaid', 'Unpaid') }}
                        </p>
                    </div>
                </div>

                {{-- QR --}}
                <div class="flex flex-col items-center gap-3 px-5 py-7 sm:px-8">
                    <div class="rounded-xl bg-white p-3">
                        {!! $qrSvg !!}
                    </div>
                    <p class="text-sm font-medium text-cg-white">{{ ui('show_qr', 'Show QR at the gate') }}</p>
                    @if ($isCheckedIn)
                        <p class="text-xs text-cg-green">
                            ✓ {{ ui('checked_in_at', 'Checked in') }} {{ $booking->checked_in_at->format('M j, H:i') }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-7 flex flex-wrap justify-center gap-3">
                <a href="{{ route('booking.download', $booking->booking_code) }}" class="btn-primary">
                    <x-icon name="download" :size="17" /> {{ ui('download_ticket', 'Download ticket') }}
                </a>
                <button type="button" onclick="window.print()" class="btn-outline">
                    <x-icon name="printer" :size="17" /> {{ ui('print_ticket', 'Print ticket') }}
                </button>
                <a href="{{ route('booking.verify', $booking->booking_code) }}" class="btn-outline">
                    <x-icon name="qr-code" :size="17" /> {{ ui('verify_ticket', 'Verify ticket') }}
                </a>
                <a href="{{ route('movies.index') }}" class="btn-ghost">
                    {{ ui('book_another', 'Book another movie') }}
                </a>
            </div>

            @auth
                <p class="mt-6 text-center text-sm text-cg-muted">
                    {{ ui('saved_to_account', 'This booking is saved to your account.') }}
                    <a href="{{ route('account.bookings') }}" class="font-semibold text-cg-red hover:underline">
                        {{ ui('my_bookings', 'My Bookings') }}
                    </a>
                </p>
            @endauth
        </div>
    </div>
@endsection
