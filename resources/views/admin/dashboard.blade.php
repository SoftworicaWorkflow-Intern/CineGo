@extends('layouts.admin')

@section('title', ui('dashboard', 'Dashboard'))

@section('content')
    {{-- KPIs --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['icon' => 'ticket', 'value' => number_format($stats['bookings_total']), 'label' => ui('total_bookings', 'Total bookings'), 'sub' => '+'.number_format($stats['bookings_today']).' '.ui('today', 'today'), 'tone' => 'red'],
            ['icon' => 'wallet', 'value' => 'Rs. '.number_format($stats['revenue_month']), 'label' => ui('revenue_this_month', 'Revenue this month'), 'sub' => number_format($stats['tickets_sold']).' '.ui('tickets', 'tickets'), 'tone' => 'green'],
            ['icon' => 'clock', 'value' => number_format($stats['showtimes_today']), 'label' => ui('showtimes_today', 'Showtimes today'), 'sub' => number_format($stats['movies']).' '.ui('movies', 'Movies'), 'tone' => 'amber'],
            ['icon' => 'mail', 'value' => number_format($stats['pending_messages']), 'label' => ui('unread_messages', 'Unread messages'), 'sub' => ui('contact_messages', 'Contact messages'), 'tone' => 'purple'],
        ] as $stat)
            <div class="card flex items-start gap-4 p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                    {{ $stat['tone'] === 'red' ? 'bg-cg-red/15 text-cg-red' : '' }}
                    {{ $stat['tone'] === 'green' ? 'bg-cg-green/15 text-cg-green' : '' }}
                    {{ $stat['tone'] === 'amber' ? 'bg-cg-amber/15 text-cg-amber' : '' }}
                    {{ $stat['tone'] === 'purple' ? 'bg-purple-500/15 text-purple-400' : '' }}">
                    <x-icon :name="$stat['icon']" :size="21" />
                </span>
                <span class="min-w-0">
                    <span class="block truncate font-display text-2xl tracking-wide text-cg-white">{{ $stat['value'] }}</span>
                    <span class="block text-xs text-cg-muted">{{ $stat['label'] }}</span>
                    <span class="mt-0.5 block text-[11px] text-cg-dim">{{ $stat['sub'] }}</span>
                </span>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
        @foreach ([
            ['key' => 'booking_trend', 'type' => 'bar', 'title' => 'Bookings & revenue', 'description' => 'Daily activity over the last 14 days', 'span' => 'lg:col-span-2 2xl:col-span-2', 'height' => 'h-80'],
            ['key' => 'booking_status', 'type' => 'doughnut', 'title' => 'Booking status', 'description' => 'Confirmed, pending, and cancelled bookings', 'span' => '', 'height' => 'h-64'],
            ['key' => 'payment_outcomes', 'type' => 'pie', 'title' => 'Payment outcomes', 'description' => 'Successful, pending, and failed payments', 'span' => '', 'height' => 'h-64'],
            ['key' => 'tickets_by_movie', 'type' => 'bar', 'title' => 'Tickets by movie', 'description' => 'Top movies by confirmed seats sold', 'span' => '', 'height' => 'h-64'],
            ['key' => 'revenue_by_movie', 'type' => 'bar', 'title' => 'Revenue by movie', 'description' => 'Top movies by successful payment revenue', 'span' => '', 'height' => 'h-64'],
            ['key' => 'bookings_by_cinema', 'type' => 'bar', 'title' => 'Bookings by cinema', 'description' => 'Top cinemas by confirmed bookings', 'span' => '', 'height' => 'h-64'],
            ['key' => 'bookings_by_weekday', 'type' => 'bar', 'title' => 'Bookings by weekday', 'description' => 'Booking activity over the last 7 days', 'span' => '', 'height' => 'h-64'],
            ['key' => 'payment_methods', 'type' => 'doughnut', 'title' => 'Payment methods', 'description' => 'Payment mix for confirmed bookings', 'span' => '', 'height' => 'h-64'],
        ] as $chartCard)
            <section class="card {{ $chartCard['span'] }} p-5 transition duration-300 hover:-translate-y-0.5 hover:border-cg-red/40">
                <div class="mb-4">
                    <h2 class="font-semibold text-cg-white">{{ $chartCard['title'] }}</h2>
                    <p class="text-xs text-cg-dim">{{ $chartCard['description'] }}</p>
                </div>
                <div class="relative {{ $chartCard['height'] }}">
                    <canvas
                        data-dashboard-chart="{{ $chartCard['key'] }}"
                        data-chart-type="{{ $chartCard['type'] }}"
                        role="img"
                        aria-label="{{ $chartCard['title'] }}"
                    ></canvas>
                </div>
            </section>
        @endforeach
    </div>

    <script type="application/json" id="dashboard-chart-data">{!! json_encode($charts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    {{-- Quick actions --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['href' => route('admin.showtimes.create'), 'icon' => 'plus', 'label' => ui('add_showtime', 'Add showtime')],
            ['href' => route('admin.movies.create'), 'icon' => 'film', 'label' => ui('add_movie', 'Add movie')],
            ['href' => route('admin.offers.create'), 'icon' => 'tag', 'label' => ui('add_offer', 'Add offer')],
            ['href' => route('admin.messages.index'), 'icon' => 'mail', 'label' => ui('view_messages', 'View messages')],
        ] as $action)
            <a href="{{ $action['href'] }}" class="card card-hover flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-cg-red/15 text-cg-red">
                    <x-icon :name="$action['icon']" :size="19" />
                </span>
                <span class="text-sm font-semibold text-cg-white">{{ $action['label'] }}</span>
                <x-icon name="chevron-right" :size="16" class="ml-auto text-cg-dim" />
            </a>
        @endforeach
    </div>
@endsection
