@extends('layouts.app')

@section('title', ui('home', 'Home'))

@section('content')
    {{-- ============================================================
         Section 2: Featured movie hero
    ============================================================ --}}
    @if ($featured->isNotEmpty())
        @php($movie = $featured->first())
        <section
            class="cg-home-hero relative flex min-h-[100vh] min-h-[100svh] w-full items-end overflow-hidden bg-cover bg-center bg-no-repeat lg:items-center"
            aria-label="Featured movie: {{ $movie->localised_title() }}"
            data-no-scroll-reveal
            data-hero-pagination
        >
            <div
                class="cg-home-hero-art cg-home-hero-art-active"
                aria-hidden="true"
                style="background-image: url('{{ asset('img/hero/ghar-jwai.png') }}')"
                data-hero-art
            ></div>
            <div class="cg-home-hero-art" aria-hidden="true" data-hero-art></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/10 lg:bg-gradient-to-r lg:from-black/80 lg:via-black/45 lg:to-transparent"></div>

            <div class="container-page relative z-10 w-full py-16 sm:py-20 lg:py-24">
                <div class="max-w-2xl">
                    <div class="mb-4 flex flex-wrap items-center gap-2" data-hero-badges>
                        @if ($movie->rating)
                            <span class="badge-dark">
                                <x-icon name="star" :size="12" class="cg-star" />
                                {{ number_format($movie->rating, 1) }}
                            </span>
                        @endif
                        @if ($movie->certificate)
                            <span class="badge-dark">{{ $movie->certificate }}</span>
                        @endif
                        <span class="badge-red">{{ $movie->genre_label() }}</span>
                    </div>

                    <h1 class="font-display text-5xl leading-[0.95] tracking-wide text-white sm:text-6xl lg:text-7xl" data-hero-title>
                        {{ $movie->localised_title() }}
                    </h1>

                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-white/85" data-hero-metadata>
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="clock" :size="15" class="text-cg-red" /><span data-hero-duration>{{ $movie->duration_label() }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="globe" :size="15" class="text-cg-red" /><span data-hero-language>{{ $movie->language }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="calendar" :size="15" class="text-cg-red" /><span data-hero-release-date>{{ $movie->release_date?->format('M j, Y') }}</span>
                        </span>
                    </div>

                    <p class="mt-4 line-clamp-3 max-w-xl text-sm leading-relaxed text-white/85 sm:text-base" data-hero-synopsis>
                        {{ $movie->localised_synopsis() }}
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3" data-hero-actions>
                        <a href="{{ route('movies.show', $movie) }}#showtimes" class="btn-primary btn-lg" data-hero-book-link aria-label="Book {{ $movie->localised_title() }} tickets">
                            <x-icon name="ticket" :size="18" /> {{ ui('hero_book', 'Book Tickets') }}
                        </a>
                        @if ($movie->trailer_url)
                            <button type="button" data-trailer="{{ $movie->trailer_url }}" class="btn-outline btn-lg">
                                <x-icon name="play" :size="17" /> {{ ui('watch_trailer', 'Watch Trailer') }}
                            </button>
                        @endif
                    </div>
                </div>

                <nav class="mt-10 flex items-center gap-2" aria-label="Featured movie pagination">
                    <button
                        type="button"
                        class="group flex h-11 items-center"
                        aria-label="Show {{ $movie->localised_title() }}"
                        aria-current="true"
                        data-hero-page
                        data-hero-image="{{ asset('img/hero/ghar-jwai.png') }}"
                        data-hero-title="{{ $movie->localised_title() }}"
                        data-hero-synopsis="{{ $movie->localised_synopsis() }}"
                        data-hero-details="true"
                        data-hero-badges="true"
                        data-hero-duration="{{ $movie->duration_label() }}"
                        data-hero-language="{{ $movie->language }}"
                        data-hero-release-date="{{ $movie->release_date?->format('M j, Y') }}"
                        data-hero-book-url="{{ route('movies.show', $movie) }}#showtimes"
                        data-hero-book-title="{{ $movie->localised_title() }}"
                    >
                        <span class="h-1 w-8 rounded-full bg-cg-red transition-all duration-300" data-hero-indicator></span>
                    </button>
                    <button
                        type="button"
                        class="group flex h-11 items-center"
                        aria-label="Show Unko Sweater"
                        aria-current="false"
                        data-hero-page
                        data-hero-image="{{ asset('img/hero/unko-sweater.png') }}"
                        data-hero-title="Unko Sweater"
                        data-hero-synopsis="Against a Himalayan winter, a hand-knit sweater weaves together love, longing, and the feeling of home."
                        data-hero-details="true"
                        data-hero-badges="false"
                        data-hero-duration="1h 48m"
                        data-hero-language="Nepali"
                        data-hero-release-date="Nov 2, 2026"
                        data-hero-book-url="{{ route('movies.show', $movie) }}#showtimes"
                        data-hero-book-title="Gharjwai"
                    >
                        <span class="h-1 w-3 rounded-full bg-white/45 transition-all duration-300 group-hover:bg-white/75" data-hero-indicator></span>
                    </button>
                    <button
                        type="button"
                        class="group flex h-11 items-center"
                        aria-label="Show Bha"
                        aria-current="false"
                        data-hero-page
                        data-hero-image="{{ asset('img/hero/bha.png') }}"
                        data-hero-title="Bha"
                        data-hero-synopsis="A gripping Nepali drama of courage and destiny, where the past shapes every choice and every journey."
                        data-hero-details="false"
                        data-hero-badges="false"
                    >
                        <span class="h-1 w-3 rounded-full bg-white/45 transition-all duration-300 group-hover:bg-white/75" data-hero-indicator></span>
                    </button>
                </nav>
            </div>
        </section>
    @endif

    {{-- ============================================================
         Section 3: Movie search
    ============================================================ --}}
    <section class="container-page relative z-10 -mt-10">
        <form
            action="{{ route('movies.index') }}"
            method="GET"
            class="card grid items-center gap-3 p-4 sm:p-5 md:grid-cols-[2fr_1fr_1fr_1fr_auto]"
        >
            <div class="flex h-11 min-h-11 items-center gap-3 rounded-lg border border-cg-border bg-white px-3.5 transition focus-within:border-cg-red focus-within:ring-2 focus-within:ring-cg-red/30">
                <x-icon name="search" :size="17" class="shrink-0 text-cg-red" />
                <label class="sr-only" for="home-search">{{ ui('search_movies', 'Search movies') }}</label>
                <input
                    id="home-search"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="{{ ui('search_placeholder', 'Search movies') }}"
                    class="h-full min-h-0 w-full border-0 bg-transparent p-0 text-sm text-cg-white placeholder:text-cg-dim focus:border-transparent focus:outline-none focus:ring-0"
                >
            </div>

            <label class="sr-only" for="home-genre">{{ ui('genre', 'Genre') }}</label>
            <select id="home-genre" name="genre" class="input h-11 min-h-11 cursor-pointer py-2.5">
                <option value="">{{ ui('genre', 'Genre') }}</option>
                @foreach ($genreOptions as $genre)
                    <option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="home-date">{{ ui('select_date', 'Select date') }}</label>
            <select id="home-date" name="show_date" class="input h-11 min-h-11 cursor-pointer py-2.5">
                <option value="">{{ ui('select_date', 'Select date') }}</option>
                @for ($day = 0; $day < 7; $day++)
                    @php($date = now()->addDays($day))
                    <option value="{{ $date->toDateString() }}" @selected(request('show_date') === $date->toDateString())>
                        {{ $date->isToday() ? ui('today', 'Today') : $date->format('D, M j') }}
                    </option>
                @endfor
            </select>

            <label class="sr-only" for="home-time">{{ ui('show_time', 'Show time') }}</label>
            <select id="home-time" name="show_time" class="input h-11 min-h-11 cursor-pointer py-2.5">
                <option value="">{{ ui('show_time', 'Show time') }}</option>
                @foreach (['10:30', '13:15', '16:00', '19:00', '21:30'] as $time)
                    <option value="{{ $time }}" @selected(request('show_time') === $time)>
                        {{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A') }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn-primary h-11 min-h-11 py-2.5">
                <x-icon name="search" :size="16" /> {{ ui('search', 'Search') }}
            </button>
        </form>
    </section>

    {{-- ============================================================
         Section 4: Now Showing
    ============================================================ --}}
    @if ($nowShowing->isNotEmpty())
        <section class="container-page pt-14">
            <x-section-head
                :kicker="ui('explore_movies', 'Explore Movies')"
                :title="ui('now_showing_title', 'Now Playing')"
                :subtitle="ui('now_showing_sub', 'Book your favourite movies playing this week.')"
                :href="route('movies.index', ['tab' => 'now_showing'])"
                :label="ui('see_all', 'See all')"
            />

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                @foreach ($nowShowing as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================
         Section 5: Coming Soon
    ============================================================ --}}
    @if ($comingSoon->isNotEmpty())
        <section class="container-page pt-16">
            <x-section-head
                :kicker="ui('coming_soon', 'Coming Soon')"
                :title="ui('coming_soon_title', 'Next Releases')"
                :subtitle="ui('coming_soon_sub', 'Get ready for the movies arriving soon.')"
                :href="route('movies.index', ['tab' => 'coming_soon'])"
                :label="ui('see_all', 'See all')"
            />

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 lg:gap-5">
                @foreach ($comingSoon as $movie)
                    <x-movie-card :movie="$movie" :show-book="false" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================
         Section 6: Browse by Genre
    ============================================================ --}}
    @if (! empty($genres))
        <section class="container-page pt-16">
            <x-section-head
                :kicker="ui('browse_genre', 'Browse by Genre')"
                :title="ui('browse_genre_sub', 'Pick a mood and jump straight to the movies.')"
            />

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:gap-4">
                @foreach ($genres as $item)
                    <a
                        href="{{ route('movies.index', ['genre' => $item['genre']]) }}"
                        class="card card-hover group flex items-center justify-between gap-3 px-4 py-5"
                    >
                        <span>
                            <span class="block font-semibold text-cg-white transition group-hover:text-cg-red">
                                {{ $item['genre'] }}
                            </span>
                            <span class="text-xs text-cg-dim">{{ $item['count'] }} {{ ui('movies', 'Movies') }}</span>
                        </span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-cg-red/15 text-cg-red transition group-hover:bg-cg-red group-hover:text-white">
                            <x-icon name="chevron-right" :size="17" />
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================
         Section 8: Special Offers
    ============================================================ --}}
    @if ($offers->isNotEmpty())
        <section class="container-page pt-16">
            <x-section-head
                :kicker="ui('special_offers', 'Special Offers')"
                :title="ui('special_offers_sub', 'Save more with our deals and promo codes.')"
                :href="route('offers.index')"
                :label="ui('view_all', 'View all')"
            />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($offers as $offer)
                    <article class="card card-hover relative overflow-hidden p-5">
                        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-cg-red/15 blur-2xl"></div>

                        <div class="relative flex items-start justify-between gap-3">
                            <span class="badge-red">{{ $offer->badge_label() }}</span>
                            @if ($offer->code)
                                <button
                                    type="button"
                                    data-copy="{{ $offer->code }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-cg-red/60 bg-cg-red/10 px-3 py-1.5 font-mono text-xs font-bold text-cg-red transition hover:bg-cg-red/20"
                                >
                                    {{ $offer->code }}
                                    <x-icon name="copy" :size="13" />
                                </button>
                            @endif
                        </div>

                        <h3 class="relative mt-3 text-lg font-semibold text-cg-white">{{ $offer->localised_title() }}</h3>
                        <p class="relative mt-1.5 line-clamp-3 text-sm text-cg-muted">{{ $offer->localised_description() }}</p>
                        <p class="relative mt-4 text-xs text-cg-dim">{{ $offer->valid_label() }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================
         Section 9: Why Choose CineGo?
    ============================================================ --}}
    <section class="container-page pt-16">
        <x-section-head
            :kicker="ui('why_title', 'Why Choose CineGo?')"
            :title="ui('why_sub', 'Booking a ticket should take less than a minute.')"
        />

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['icon' => 'zap', 'title' => ui('why_1_title', 'Easy online booking'), 'text' => ui('why_1_text', 'A clear, guided flow — pick a movie, pick seats, done.')],
                ['icon' => 'shield-check', 'title' => ui('why_2_title', 'Secure payment'), 'text' => ui('why_2_text', 'eSewa, Khalti and card payments verified on our server.')],
                ['icon' => 'qr-code', 'title' => ui('why_3_title', 'Instant digital tickets'), 'text' => ui('why_3_text', 'Your QR ticket appears immediately after confirmation.')],
                ['icon' => 'armchair', 'title' => ui('why_4_title', 'Convenient seat selection'), 'text' => ui('why_4_text', 'See exactly which seats are free before you pay.')],
                ['icon' => 'users', 'title' => ui('why_5_title', 'Multiple cinema branches'), 'text' => ui('why_5_text', 'CineGo halls across Nepal, filtered by your city.')],
                ['icon' => 'globe', 'title' => ui('why_6_title', 'English & Nepali support'), 'text' => ui('why_6_text', 'Use the website in English or नेपाली, your choice.')],
            ] as $feature)
                <div class="card card-hover flex gap-4 p-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cg-red/15 text-cg-red">
                        <x-icon :name="$feature['icon']" :size="23" />
                    </span>
                    <span>
                        <h3 class="font-semibold text-cg-white">{{ $feature['title'] }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-cg-muted">{{ $feature['text'] }}</p>
                    </span>
                </div>
            @endforeach
        </div>
    </section>
@endsection
