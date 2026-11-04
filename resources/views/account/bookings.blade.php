@extends('layouts.app')

@section('title', ui('my_bookings', 'My Bookings'))

@section('content')
    <div class="container-page py-10">
        <x-breadcrumbs :items="[['label' => ui('my_account', 'My Account'), 'href' => route('account.dashboard')], ['label' => ui('my_bookings', 'My Bookings')]]" />

        <div class="mb-8">
            <p class="section-kicker">{{ ui('my_account', 'My Account') }}</p>
            <h1 class="section-title">{{ ui('my_bookings', 'My Bookings') }}</h1>
        </div>

        @include('partials.account-nav')

        {{-- Tabs --}}
        <div class="mb-6 flex gap-2">
            @foreach (['upcoming' => ui('upcoming_tickets', 'Upcoming tickets'), 'history' => ui('booking_history', 'Booking history'), 'all' => ui('all', 'All')] as $key => $label)
                <a
                    href="{{ route('account.bookings', ['tab' => $key]) }}"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition
                        {{ $tab === $key ? 'border-cg-red bg-cg-red text-white' : 'border-cg-border bg-cg-charcoal text-cg-muted hover:border-cg-red hover:text-white' }}"
                >{{ $label }}</a>
            @endforeach
        </div>

        @if ($bookings->isEmpty())
            <x-empty
                :title="ui('no_bookings_yet', 'You have not booked anything yet.')"
                :text="ui('browse_now', 'Browse movies')"
                icon="ticket"
                :action-label="ui('explore_movies', 'Explore Movies')"
                :action-href="route('movies.index')"
            />
        @else
            <ul class="space-y-4">
                @foreach ($bookings as $booking)
                    <li class="card overflow-hidden">
                        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                            <img src="{{ optional($booking->movie)->poster_url ?? '/img/posters/poster-1.svg' }}" alt="" class="h-24 w-16 shrink-0 rounded-lg object-cover" loading="lazy">

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('account.booking', $booking) }}" class="font-semibold text-cg-white transition hover:text-cg-red">
                                        {{ $booking->movie_title }}
                                    </a>
                                    <span class="badge-dark font-mono">{{ $booking->booking_code }}</span>
                                    <span class="{{ $booking->status === \App\Models\Booking::STATUS_CONFIRMED ? 'badge-green' : ($booking->status === \App\Models\Booking::STATUS_CANCELLED ? 'badge-red' : 'badge-amber') }}">
                                        {{ $booking->status_label() }}
                                    </span>
                                    @if ($booking->checked_in_at)
                                        <span class="badge-dark">{{ ui('used', 'Used') }}</span>
                                    @endif
                                </div>

                                <p class="mt-1.5 text-sm text-cg-muted">
                                    {{ $booking->cinema_name }} · {{ $booking->hall_name }}
                                </p>
                                <p class="mt-1 flex flex-wrap gap-x-4 text-xs text-cg-dim">
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-icon name="calendar" :size="13" class="text-cg-red" />
                                        {{ $booking->show_date->format('D, M j, Y') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-icon name="clock" :size="13" class="text-cg-red" />
                                        {{ \Illuminate\Support\Str::of($booking->start_time)->substr(0, 5) }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-cg-red">
                                        <x-icon name="armchair" :size="13" />
                                        {{ $booking->seat_labels() }}
                                    </span>
                                </p>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-2 sm:flex-col sm:items-end">
                                <span class="font-display text-xl tracking-wide text-cg-white">Rs. {{ number_format($booking->total_amount) }}</span>
                                <a href="{{ route('account.booking', $booking) }}" class="btn-primary px-4 py-2 text-xs">
                                    <x-icon name="qr-code" :size="14" /> {{ ui('view_ticket', 'View ticket') }}
                                </a>
                                <a href="{{ route('booking.download', $booking->booking_code) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    <x-icon name="download" :size="13" /> {{ ui('download_ticket', 'Download') }}
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
@endsection
