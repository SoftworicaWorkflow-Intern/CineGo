@extends('layouts.app')

@section('title', ui('verify_ticket', 'Verify ticket'))

@php
    $isCheckedIn = $booking->checked_in_at !== null;
    $isCancelled = $booking->status === \App\Models\Booking::STATUS_CANCELLED;
@endphp

@section('content')
    <div class="container-page py-10">
        <div class="mx-auto max-w-2xl">
            <div class="mb-7 text-center">
                <span class="mx-auto mb-4 inline-flex"><x-logo :size="44" /></span>

                @if ($isCancelled)
                    <div class="rounded-xl border border-cg-red/50 bg-cg-red/10 px-5 py-4">
                        <p class="font-display text-3xl tracking-wide text-cg-red">{{ ui('invalid', 'INVALID') }}</p>
                        <p class="mt-1 text-sm text-cg-muted">{{ ui('booking_cancelled_text', 'This booking has been cancelled.') }}</p>
                    </div>
                @elseif ($isCheckedIn)
                    <div class="rounded-xl border border-cg-amber/50 bg-cg-amber/10 px-5 py-4">
                        <p class="font-display text-3xl tracking-wide text-cg-amber">{{ ui('already_used', 'ALREADY USED') }}</p>
                        <p class="mt-1 text-sm text-cg-muted">
                            {{ ui('checked_in_at', 'Checked in') }} {{ $booking->checked_in_at->format('M j, Y H:i') }}
                        </p>
                    </div>
                @else
                    <div class="rounded-xl border border-cg-green/50 bg-cg-green/10 px-5 py-4">
                        <p class="font-display text-3xl tracking-wide text-cg-green">{{ ui('valid', 'VALID TICKET') }}</p>
                        <p class="mt-1 text-sm text-cg-muted">{{ ui('ready_for_entry', 'Ready for entry.') }}</p>
                    </div>
                @endif
            </div>

            <div class="card overflow-hidden">
                <div class="border-b border-cg-border-soft bg-gradient-to-r from-cg-red/20 to-transparent px-6 py-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-display text-2xl tracking-wide text-cg-white">{{ $booking->movie_title }}</p>
                        </div>
                        <span class="badge-dark font-mono">{{ $booking->booking_code }}</span>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 px-6 py-6 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('dates', 'Date') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ $booking->show_date->format('D, M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('times', 'Time') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ \Illuminate\Support\Str::of($booking->start_time)->substr(0, 5) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('seats', 'Seats') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ $booking->seat_labels() }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('customer_info', 'Customer') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ $booking->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('mobile_number', 'Mobile') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">{{ $booking->customer_phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-cg-dim">{{ ui('total', 'Total') }}</dt>
                        <dd class="mt-1 font-semibold text-cg-white">Rs. {{ number_format($booking->total_amount) }}</dd>
                    </div>
                </dl>

                @if (! $isCheckedIn && ! $isCancelled)
                    <div class="border-t border-cg-border-soft px-6 py-5">
                        @auth
                            <form action="{{ route('booking.checkin', $booking->booking_code) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary btn-lg btn-block">
                                    <x-icon name="check-circle" :size="18" /> {{ ui('mark_used', 'Mark ticket as used') }}
                                </button>
                            </form>
                            <p class="mt-3 text-center text-xs text-cg-dim">
                                {{ ui('checkin_once_note', 'Checking in is permanent and cannot be repeated.') }}
                            </p>
                        @else
                            <p class="text-center text-sm text-cg-muted">
                                {{ ui('staff_login_note', 'Staff sign-in is required to check a ticket in.') }}
                                <a href="{{ route('login') }}" class="font-semibold text-cg-red hover:underline">{{ ui('login', 'Login') }}</a>
                            </p>
                        @endauth
                    </div>
                @endif
            </div>

            <p class="mt-6 text-center">
                <a href="{{ route('home') }}" class="text-sm font-medium text-cg-red hover:underline">
                    ← {{ ui('go_home', 'Go to homepage') }}
                </a>
            </p>
        </div>
    </div>
@endsection
