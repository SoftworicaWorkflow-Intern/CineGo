@extends('layouts.admin')

@section('title', ui('my_bookings', 'Bookings'))

@php
    $statusTabs = [
        '' => ['label' => ui('all', 'All'), 'count' => $counts['all']],
        'confirmed' => ['label' => ui('confirmed', 'Confirmed'), 'count' => $counts['confirmed']],
        'pending' => ['label' => ui('pending', 'Pending'), 'count' => $counts['pending']],
        'cancelled' => ['label' => ui('cancelled', 'Cancelled'), 'count' => $counts['cancelled']],
    ];
@endphp

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach ($statusTabs as $key => $tab)
                <a href="{{ route('admin.bookings.index', array_filter(['status' => $key, 'q' => $term])) }}"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition
                        {{ (string) ($status ?? '') === $key ? 'border-cg-red bg-cg-red text-white' : 'border-cg-border bg-cg-charcoal text-cg-muted hover:border-cg-red hover:text-white' }}">
                    {{ $tab['label'] }}
                    <span class="ml-1 text-xs opacity-70">{{ number_format($tab['count']) }}</span>
                </a>
            @endforeach
        </div>

        <form action="{{ route('admin.bookings.index') }}" method="GET" class="flex gap-2">
            @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="relative">
                <x-icon name="search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-cg-red" />
                <input type="search" name="q" value="{{ $term }}" placeholder="{{ ui('search_booking_hint', 'Code, name, phone, movie…') }}" class="input h-11 min-h-11 pl-9 sm:w-64">
            </div>
            <button type="submit" class="btn-outline px-4">{{ ui('search', 'Search') }}</button>
        </form>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full min-w-[880px] text-sm">
            <thead>
                <tr class="border-b border-cg-border-soft text-left text-xs uppercase tracking-wide text-cg-dim">
                    <th class="px-5 py-4 font-semibold">{{ ui('booking_id', 'Booking') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('movies', 'Movie') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('customer_info', 'Customer') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('show', 'Show') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('seats', 'Seats') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('total', 'Amount') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('status', 'Status') }}</th>
                    <th class="px-5 py-4 text-right font-semibold">{{ ui('actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cg-border-soft">
                @forelse ($bookings as $booking)
                    <tr class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="block font-mono text-xs font-bold text-cg-white hover:text-cg-red">
                                {{ $booking->booking_code }}
                            </a>
                            <span class="block text-[11px] text-cg-dim">{{ $booking->created_at->format('M j, H:i') }}</span>
                        </td>
                        <td class="px-5 py-4 text-cg-white">{{ $booking->movie_title }}</td>
                        <td class="px-5 py-4">
                            <span class="block text-cg-muted">{{ $booking->customer_name }}</span>
                            <span class="block text-xs text-cg-dim">{{ $booking->customer_phone }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="block text-cg-muted">{{ $booking->show_date->format('M j, Y') }}</span>
                            <span class="block text-xs text-cg-dim">{{ \Illuminate\Support\Str::of($booking->start_time)->substr(0, 5) }} · {{ $booking->cinema_name }}</span>
                        </td>
                        <td class="px-5 py-4 text-xs text-cg-red">{{ $booking->seat_labels() }}</td>
                        <td class="px-5 py-4 font-semibold text-cg-white">Rs. {{ number_format($booking->total_amount) }}</td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="{{ $booking->status === \App\Models\Booking::STATUS_CONFIRMED ? 'badge-green' : ($booking->status === \App\Models\Booking::STATUS_CANCELLED ? 'badge-red' : 'badge-amber') }}">
                                    {{ $booking->status_label() }}
                                </span>
                                @if ($booking->is_paid)
                                    <span class="badge-dark">{{ ui('paid', 'Paid') }}</span>
                                @endif
                                @if ($booking->checked_in_at)
                                    <span class="badge-dark">{{ ui('used', 'Used') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                <x-icon name="eye" :size="14" /> {{ ui('view_details', 'View') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-cg-muted">{{ ui('no_results', 'Nothing matched your search.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
@endsection
