@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', ui('dashboard', 'Dashboard'))

@section('content')
    <div class="container-page py-10">
        <x-breadcrumbs :items="[['label' => ui('my_account', 'My Account')], ['label' => ui('dashboard', 'Dashboard')]]" />

        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-kicker">{{ ui('welcome_back', 'Welcome back') }}</p>
                <h1 class="section-title">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-cg-muted">
                    {{ ui('member_since', 'Member since') }} {{ $user->created_at->format('M Y') }}
                    @if ($user->phone) · {{ $user->phone }}@endif
                </p>
            </div>
            <a href="{{ route('movies.index') }}" class="btn-primary">
                <x-icon name="film" :size="17" /> {{ ui('browse_now', 'Browse movies') }}
            </a>
        </div>

        @include('partials.account-nav')

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => 'ticket', 'value' => $stats['bookings'], 'label' => ui('total_bookings', 'Total bookings'), 'tone' => 'red'],
                ['icon' => 'calendar', 'value' => $stats['upcoming'], 'label' => ui('upcoming', 'Upcoming'), 'tone' => 'green'],
                ['icon' => 'wallet', 'value' => 'Rs. '.number_format($stats['spent']), 'label' => ui('total_spent', 'Total spent'), 'tone' => 'amber'],
                ['icon' => 'heart', 'value' => $stats['favorites'], 'label' => ui('saved_movies', 'Saved movies'), 'tone' => 'purple'],
            ] as $stat)
                <div class="card flex items-center gap-4 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                        {{ $stat['tone'] === 'red' ? 'bg-cg-red/15 text-cg-red' : '' }}
                        {{ $stat['tone'] === 'green' ? 'bg-cg-green/15 text-cg-green' : '' }}
                        {{ $stat['tone'] === 'amber' ? 'bg-cg-amber/15 text-cg-amber' : '' }}
                        {{ $stat['tone'] === 'purple' ? 'bg-purple-500/15 text-purple-400' : '' }}">
                        <x-icon :name="$stat['icon']" :size="21" />
                    </span>
                    <span>
                        <span class="block font-display text-2xl tracking-wide text-cg-white">{{ $stat['value'] }}</span>
                        <span class="block text-xs text-cg-muted">{{ $stat['label'] }}</span>
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_320px]">
            <div class="space-y-6">
                {{-- Upcoming tickets --}}
                <section class="card p-5 sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="font-semibold text-cg-white">{{ ui('upcoming_tickets', 'Upcoming tickets') }}</h2>
                        <a href="{{ route('account.bookings') }}" class="text-xs font-semibold text-cg-red hover:underline">
                            {{ ui('view_all', 'View all') }}
                        </a>
                    </div>

                    @if ($upcoming->isEmpty())
                        <p class="rounded-lg border border-cg-border bg-cg-charcoal px-4 py-6 text-center text-sm text-cg-muted">
                            {{ ui('no_upcoming', 'No upcoming shows.') }}
                            <a href="{{ route('movies.index') }}" class="font-semibold text-cg-red hover:underline">
                                {{ ui('browse_now', 'Browse movies') }}
                            </a>
                        </p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($upcoming as $booking)
                                <li>
                                    <a href="{{ route('account.booking', $booking) }}" class="flex items-center gap-4 rounded-xl border border-cg-border bg-cg-charcoal p-3.5 transition hover:border-cg-red">
                                        <img src="{{ optional($booking->movie)->poster_url ?? '/img/posters/poster-1.svg' }}" alt="" class="h-16 w-11 rounded-md object-cover" loading="lazy">
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-semibold text-cg-white">{{ $booking->movie_title }}</span>
                                            <span class="mt-0.5 block truncate text-xs text-cg-muted">
                                                {{ $booking->show_date->format('M j, Y') }} · {{ \Illuminate\Support\Str::of($booking->start_time)->substr(0, 5) }}
                                            </span>
                                            <span class="mt-1 block text-xs font-semibold text-cg-red">{{ $booking->seat_labels() }}</span>
                                        </span>
                                        <x-icon name="chevron-right" :size="17" class="text-cg-dim" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                {{-- Recent bookings --}}
                <section class="card p-5 sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="font-semibold text-cg-white">{{ ui('booking_history', 'Booking history') }}</h2>
                        <a href="{{ route('account.bookings', ['tab' => 'history']) }}" class="text-xs font-semibold text-cg-red hover:underline">
                            {{ ui('view_all', 'View all') }}
                        </a>
                    </div>

                    @if ($recent->isEmpty())
                        <p class="rounded-lg border border-cg-border bg-cg-charcoal px-4 py-6 text-center text-sm text-cg-muted">
                            {{ ui('no_bookings_yet', 'You have not booked anything yet.') }}
                        </p>
                    @else
                        <ul class="divide-y divide-cg-border-soft">
                            @foreach ($recent as $booking)
                                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-medium text-cg-white">{{ $booking->movie_title }}</span>
                                        <span class="block text-xs text-cg-dim">
                                            {{ $booking->booking_code }} · {{ $booking->show_date->format('M j, Y') }}
                                        </span>
                                    </span>
                                    <span class="flex items-center gap-3">
                                        <span class="badge-dark">Rs. {{ number_format($booking->total_amount) }}</span>
                                        <span class="{{ $booking->status === \App\Models\Booking::STATUS_CONFIRMED ? 'badge-green' : ($booking->status === \App\Models\Booking::STATUS_CANCELLED ? 'badge-red' : 'badge-amber') }}">
                                            {{ $booking->status_label() }}
                                        </span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                <div class="card p-5">
                    <h2 class="mb-4 font-semibold text-cg-white">{{ ui('favorite_movies', 'Favourite movies') }}</h2>

                    @if ($favorites->isEmpty())
                        <p class="text-sm text-cg-muted">{{ ui('no_favorites', 'No favourite movies yet.') }}</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($favorites as $movie)
                                <a href="{{ route('movies.show', $movie) }}" class="flex items-center gap-3 rounded-lg p-1 transition hover:bg-cg-charcoal">
                                    <img src="{{ $movie->poster_url }}" alt="" class="h-16 w-11 rounded-md object-cover" loading="lazy">
                                    <span class="min-w-0">
                                        <span class="line-clamp-1 block text-sm font-medium text-cg-white">{{ $movie->localised_title() }}</span>
                                        <span class="block text-xs text-cg-dim">{{ $movie->genre_label() }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('account.favorites') }}" class="btn-ghost mt-3 w-full justify-center text-xs">
                            {{ ui('view_all', 'View all') }}
                        </a>
                    @endif
                </div>

                <div class="card p-5">
                    <h2 class="mb-3 font-semibold text-cg-white">{{ ui('quick_links', 'Quick links') }}</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('account.payments') }}" class="text-cg-muted transition hover:text-cg-red">{{ ui('payment_history', 'Payment history') }}</a></li>
                        <li><a href="{{ route('account.notifications') }}" class="text-cg-muted transition hover:text-cg-red">{{ ui('notifications', 'Notifications') }}</a></li>
                        <li><a href="{{ route('account.settings') }}" class="text-cg-muted transition hover:text-cg-red">{{ ui('language_preference', 'Language preference') }}</a></li>
                        <li><a href="{{ route('pages.contact') }}" class="text-cg-muted transition hover:text-cg-red">{{ ui('help_support', 'Help & support') }}</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endsection
