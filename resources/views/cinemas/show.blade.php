@extends('layouts.app')

@section('title', $cinema->localised_name())

@php($dateKeys = $groupedShowtimes->keys()->values())

@section('content')
    {{-- Branch banner --}}
    <div class="relative overflow-hidden border-b border-cg-border-soft bg-gradient-to-br from-cg-red/20 via-cg-charcoal to-cg-black surface-grid">
        <div class="absolute inset-0 bg-gradient-to-t from-cg-black via-cg-black/70 to-transparent"></div>

        <div class="container-page relative pb-10 pt-8">
            <x-breadcrumbs :items="[['label' => ui('cinemas', 'Cinemas'), 'href' => route('cinemas.index')], ['label' => $cinema->localised_name()]]" />

            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="badge-red">{{ $cinema->city?->display_name() }}</span>
                        <span class="badge-dark">{{ $cinema->halls()->active()->count() }} {{ ui('screens', 'Screens') }}</span>
                    </div>

                    <h1 class="font-display text-4xl leading-tight tracking-wide text-white sm:text-5xl">
                        {{ $cinema->localised_name() }}
                    </h1>

                    <p class="mt-3 flex items-start gap-2 text-sm text-cg-muted sm:text-base">
                        <x-icon name="map-pin" :size="16" class="mt-0.5 shrink-0 text-cg-red" />
                        {{ $cinema->localised_address() }}, {{ $cinema->city?->display_name() }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer" class="btn-outline">
                        <x-icon name="map-pin" :size="16" /> {{ ui('directions', 'Get directions') }}
                    </a>
                    @if ($cinema->phone)
                        <a href="tel:{{ $cinema->phone }}" class="btn-primary">
                            <x-icon name="phone" :size="16" /> {{ $cinema->phone }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container-page grid gap-10 pt-10 lg:grid-cols-[1fr_320px]">
        <div class="min-w-0 space-y-12">
            {{-- Now playing at this branch --}}
            @if ($movies->isNotEmpty())
                <section>
                    <x-section-head :kicker="ui('now_showing', 'Now Showing')" :title="ui('movies_here', 'Movies at this branch')" />
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                        @foreach ($movies as $movie)
                            <x-movie-card :movie="$movie" />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Showtimes --}}
            <section id="showtimes" class="scroll-mt-24">
                <x-section-head :kicker="ui('step_select_showtime', 'Showtime')" :title="ui('showtimes_for', 'Showtimes')" />

                @if ($dateKeys->isEmpty())
                    <x-empty
                        :title="ui('no_showtimes', 'No showtimes available for this selection.')"
                        :text="ui('check_back_soon', 'Check back soon for new show schedules.')"
                        icon="calendar"
                        :action-label="ui('movies', 'Movies')"
                        :action-href="route('movies.index')"
                    />
                @else
                    <div class="-mx-4 mb-6 flex gap-2 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0" role="tablist" aria-label="Show dates">
                        @foreach ($dateKeys as $index => $dateKey)
                            @php($date = \Illuminate\Support\Carbon::parse($dateKey))
                            <button
                                type="button"
                                role="tab"
                                data-date-chip="{{ $dateKey }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                class="cg-step-idle shrink-0 rounded-xl border px-5 py-3 text-center transition {{ $index === 0 ? 'cg-step-active' : '' }}"
                            >
                                <span class="block text-[11px] uppercase tracking-wide">
                                    {{ $date->isToday() ? ui('today', 'Today') : $date->format('D') }}
                                </span>
                                <span class="block text-sm font-bold">{{ $date->format('M j') }}</span>
                            </button>
                        @endforeach
                    </div>

                    @foreach ($dateKeys as $index => $dateKey)
                        <div data-date-panel="{{ $dateKey }}" class="{{ $index === 0 ? '' : 'hidden' }}">
                            <div class="space-y-4">
                                @foreach ($groupedShowtimes[$dateKey]->groupBy(fn ($showtime) => $showtime->movie_id) as $movieShowtimes)
                                    @php($movie = $movieShowtimes->first()->movie)
                                    <div class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:p-5">
                                        <img
                                            src="{{ $movie->poster_url }}"
                                            alt="{{ $movie->localised_title() }}"
                                            class="h-24 w-16 shrink-0 rounded-lg object-cover"
                                            loading="lazy"
                                        >

                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-semibold text-cg-white">
                                                <a href="{{ route('movies.show', $movie) }}" class="transition hover:text-cg-red">
                                                    {{ $movie->localised_title() }}
                                                </a>
                                            </h3>
                                            <p class="mt-0.5 text-xs text-cg-muted">
                                                {{ $movie->genre_label() }} · {{ $movie->duration_label() }} · {{ $movie->language }}
                                            </p>

                                            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                                @foreach ($movieShowtimes->sortBy('start_time') as $showtime)
                                                    <a
                                                        href="{{ route('booking.seats', $showtime) }}"
                                                        class="flex flex-col items-center rounded-lg border border-cg-border bg-cg-charcoal px-3 py-2.5 transition hover:border-cg-red hover:bg-cg-red/10"
                                                    >
                                                        <span class="text-sm font-bold text-cg-white">{{ $showtime->start_time_label() }}</span>
                                                        <span class="text-[11px] text-cg-dim">
                                                            {{ $showtime->hall->name }} · {{ $showtime->format }} · Rs. {{ number_format($showtime->price_normal) }}
                                                        </span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </section>
        </div>

        {{-- Branch info --}}
        <aside class="space-y-6">
            <div class="card space-y-4 p-5">
                <h2 class="font-semibold text-cg-white">{{ ui('branch_info', 'Branch information') }}</h2>

                <ul class="space-y-3.5 text-sm">
                    <li class="flex items-start gap-2.5 text-cg-muted">
                        <x-icon name="map-pin" :size="16" class="mt-0.5 shrink-0 text-cg-red" />
                        <span>{{ $cinema->localised_address() }}</span>
                    </li>
                    @if ($cinema->phone)
                        <li class="flex items-center gap-2.5 text-cg-muted">
                            <x-icon name="phone" :size="16" class="shrink-0 text-cg-red" />
                            <a href="tel:{{ $cinema->phone }}" class="hover:text-white">{{ $cinema->phone }}</a>
                        </li>
                    @endif
                    @if ($cinema->email)
                        <li class="flex items-center gap-2.5 text-cg-muted">
                            <x-icon name="mail" :size="16" class="shrink-0 text-cg-red" />
                            <a href="mailto:{{ $cinema->email }}" class="hover:text-white">{{ $cinema->email }}</a>
                        </li>
                    @endif
                </ul>

                @if ($cinema->description)
                    <p class="border-t border-cg-border-soft pt-4 text-sm leading-relaxed text-cg-muted">
                        {{ $cinema->description }}
                    </p>
                @endif

                @if ($cinema->facilities)
                    <div class="border-t border-cg-border-soft pt-4">
                        <p class="mb-2.5 text-xs font-semibold uppercase tracking-wide text-cg-dim">{{ ui('facilities', 'Facilities') }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($cinema->facilities as $facility)
                                <span class="rounded-md border border-cg-border bg-cg-charcoal px-2.5 py-1.5 text-xs text-cg-muted">
                                    {{ $facility }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="card space-y-3 p-5">
                <h2 class="font-semibold text-cg-white">{{ ui('screens', 'Screens') }}</h2>
                @foreach ($cinema->halls()->active()->orderBy('name')->get() as $hall)
                    <div class="flex items-center justify-between rounded-lg border border-cg-border bg-cg-charcoal px-3.5 py-2.5 text-sm">
                        <span class="flex items-center gap-2 text-cg-white">
                            <x-icon name="film" :size="15" class="text-cg-red" /> {{ $hall->name }}
                        </span>
                        <span class="text-xs text-cg-dim">
                            {{ $hall->total_rows }}×{{ $hall->seats_per_row }} {{ ui('seats', 'seats') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer" class="btn-outline btn-block">
                <x-icon name="map-pin" :size="16" /> {{ ui('directions', 'Get directions') }}
            </a>
        </aside>
    </div>

    <script>
        (function () {
            var chips = document.querySelectorAll('[data-date-chip]');
            var panels = document.querySelectorAll('[data-date-panel]');
            if (!chips.length) return;

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var date = chip.getAttribute('data-date-chip');

                    panels.forEach(function (panel) {
                        panel.classList.toggle('hidden', panel.getAttribute('data-date-panel') !== date);
                    });

                    chips.forEach(function (other) {
                        var active = other.getAttribute('data-date-chip') === date;
                        other.setAttribute('aria-selected', active ? 'true' : 'false');
                        other.classList.toggle('cg-step-active', active);
                        other.classList.toggle('cg-step-idle', !active);
                    });

                    var target = document.getElementById('showtimes');
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
        })();
    </script>
@endsection
