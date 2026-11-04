@extends('layouts.app')

@section('title', ui('cinemas', 'Cinemas'))

@section('content')
    <div class="container-page pt-10">
        <x-breadcrumbs :items="[['label' => ui('cinemas', 'Cinemas')]]" />

        <div class="mb-8">
            <p class="section-kicker">{{ ui('choose_cinema', 'Choose Your Cinema') }}</p>
            <h1 class="section-title">{{ ui('cinemas', 'Cinemas') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-cg-muted sm:text-base">
                {{ ui('cinemas_sub', 'Every CineGo branch in Nepal — pick the one closest to you.') }}
            </p>
        </div>

        {{-- City filter chips --}}
        <div class="mb-8 flex flex-wrap gap-2">
            <a
                href="{{ route('cinemas.index') }}"
                class="rounded-full border px-4 py-2 text-sm font-medium transition
                    {{ ! $selectedCity ? 'border-cg-red bg-cg-red text-white' : 'border-cg-border bg-cg-charcoal text-cg-muted hover:border-cg-red hover:text-cg-white' }}"
            >{{ ui('all_cities', 'All Cities') }}</a>

            @foreach ($cities as $city)
                <a
                    href="{{ route('cinemas.index', ['city' => $city->slug]) }}"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition
                        {{ $selectedCity === $city->slug ? 'border-cg-red bg-cg-red text-white' : 'border-cg-border bg-cg-charcoal text-cg-muted hover:border-cg-red hover:text-cg-white' }}"
                >{{ $city->display_name() }}</a>
            @endforeach
        </div>

        @if ($cinemas->isEmpty())
            <x-empty
                :title="ui('no_cinemas', 'No cinemas in this city yet.')"
                :text="ui('try_another_city', 'Try another city.')"
                icon="map-pin"
                :action-label="ui('all_cities', 'All Cities')"
                :action-href="route('cinemas.index')"
            />
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($cinemas as $cinema)
                    <article class="card card-hover flex flex-col overflow-hidden">
                        <div class="relative h-36 overflow-hidden border-b border-cg-border-soft bg-gradient-to-br from-cg-red/25 via-cg-charcoal to-cg-black surface-grid">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <x-icon name="armchair" :size="44" class="text-cg-red/70" />
                            </div>
                            <span class="badge-dark absolute left-3 top-3">{{ $cinema->city?->display_name() }}</span>
                        </div>

                        <div class="flex flex-1 flex-col p-5">
                            <h2 class="text-lg font-semibold text-cg-white">
                                <a href="{{ route('cinemas.show', $cinema) }}" class="transition hover:text-cg-red">
                                    {{ $cinema->localised_name() }}
                                </a>
                            </h2>

                            <p class="mt-1.5 flex items-start gap-2 text-sm text-cg-muted">
                                <x-icon name="map-pin" :size="15" class="mt-0.5 shrink-0 text-cg-red" />
                                {{ $cinema->localised_address() }}
                            </p>

                            <div class="mt-3 flex flex-wrap gap-4 text-xs text-cg-dim">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="film" :size="13" class="text-cg-red" />
                                    {{ $cinema->halls_count }} {{ ui('screens', 'Screens') }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="ticket" :size="13" class="text-cg-red" />
                                    {{ $cinema->showtimes_count }} {{ ui('showtimes', 'Showtimes') }}
                                </span>
                            </div>

                            @if ($cinema->facilities)
                                <div class="mt-4 flex flex-wrap gap-1.5">
                                    @foreach (array_slice($cinema->facilities, 0, 5) as $facility)
                                        <span class="rounded-md border border-cg-border bg-cg-charcoal px-2 py-1 text-[11px] text-cg-muted">
                                            {{ $facility }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-5 flex gap-2 pt-auto">
                                <a href="{{ route('cinemas.show', $cinema) }}" class="btn-outline flex-1 justify-center px-3 py-2 text-xs">
                                    {{ ui('view_details', 'View Details') }}
                                </a>
                                <a href="{{ route('cinemas.show', $cinema) }}#showtimes" class="btn-primary flex-1 justify-center px-3 py-2 text-xs">
                                    {{ ui('view_showtimes', 'Showtimes') }}
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
