@extends('layouts.app')

@section('title', ui('home', 'Home'))

@section('content')
    {{-- ============================================================
         Section 2: Hero slider
    ============================================================ --}}
    @if ($featured->isNotEmpty())
        <section
            data-slider
            data-interval="6500"
            class="relative h-[78vh] min-h-[520px] w-full overflow-hidden sm:min-h-[560px]"
            aria-label="Featured movies"
        >
            @foreach ($featured as $index => $movie)
                <div
                    data-slide
                    class="cg-slide absolute inset-0 {{ $index === 0 ? 'cg-slide-active' : '' }}"
                    aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                >
                    <img
                        src="{{ $movie->banner_url ?? $movie->poster_url }}"
                        alt="{{ $movie->localised_title() }}"
                        class="h-full w-full object-cover object-center"
                        {{ $index === 0 ? '' : 'fetchpriority="low"' }}
                    >
                    <div class="absolute inset-0 bg-gradient-to-r from-cg-black via-cg-black/85 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-cg-black via-transparent to-cg-black/40"></div>

                    <div class="container-page absolute inset-0 flex items-center">
                        <div class="max-w-2xl pb-16 pt-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
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

                            <h1 class="font-display text-5xl leading-[0.95] tracking-wide text-white sm:text-6xl lg:text-7xl">
                                {{ $movie->localised_title() }}
                            </h1>

                            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-cg-muted">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="clock" :size="15" class="text-cg-red" />{{ $movie->duration_label() }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="globe" :size="15" class="text-cg-red" />{{ $movie->language }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="calendar" :size="15" class="text-cg-red" />{{ $movie->release_date?->format('M j, Y') }}
                                </span>
                            </div>

                            <p class="mt-4 line-clamp-3 max-w-xl text-sm leading-relaxed text-cg-muted sm:text-base">
                                {{ $movie->localised_synopsis() }}
                            </p>

                            <div class="mt-7 flex flex-wrap gap-3">
                                <a href="{{ route('movies.show', $movie) }}#showtimes" class="btn-primary btn-lg">
                                    <x-icon name="ticket" :size="18" /> {{ ui('hero_book', 'Book Tickets') }}
                                </a>
                                @if ($movie->trailer_url)
                                    <button type="button" data-trailer="{{ $movie->trailer_url }}" class="btn-outline btn-lg">
                                        <x-icon name="play" :size="17" /> {{ ui('watch_trailer', 'Watch Trailer') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Manual navigation --}}
            @if ($featured->count() > 1)
                <button
                    type="button"
                    data-slide-prev
                    aria-label="Previous slide"
                    class="absolute left-3 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-cg-border bg-black/50 text-white backdrop-blur transition hover:border-cg-red hover:bg-cg-red sm:flex"
                >
                    <x-icon name="arrow-left" :size="18" />
                </button>
                <button
                    type="button"
                    data-slide-next
                    aria-label="Next slide"
                    class="absolute right-3 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-cg-border bg-black/50 text-white backdrop-blur transition hover:border-cg-red hover:bg-cg-red sm:flex"
                >
                    <x-icon name="arrow-right" :size="18" />
                </button>

                <div class="absolute bottom-6 left-1/2 flex -translate-x-1/2 items-center gap-2.5">
                    @foreach ($featured as $index => $movie)
                        <button
                            type="button"
                            data-slide-dot
                            aria-label="Go to slide {{ $index + 1 }}"
                            aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                            class="h-2.5 rounded-full transition-all {{ $index === 0 ? 'w-8 bg-cg-red' : 'w-2.5 bg-white/30' }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- ============================================================
         Section 3: Movie search
    ============================================================ --}}
    <section class="container-page relative z-10 -mt-10">
        <form
            action="{{ route('movies.index') }}"
            method="GET"
            class="card grid gap-3 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-[2fr_1fr_1fr_1fr_auto]"
        >
            <div class="relative">
                <x-icon name="search" :size="17" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-cg-red" />
                <label class="sr-only" for="home-search">{{ ui('search_movies', 'Search movies') }}</label>
                <input
                    id="home-search"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="{{ ui('search_placeholder', 'Search movie, genre, language or cinema…') }}"
                    class="input pl-10"
                >
            </div>

            <label class="sr-only" for="home-genre">{{ ui('genre', 'Genre') }}</label>
            <select id="home-genre" name="genre" class="input cursor-pointer">
                <option value="">{{ ui('genre', 'Genre') }}</option>
                @foreach ($genreOptions as $genre)
                    <option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="home-language">{{ ui('language_label', 'Language') }}</label>
            <select id="home-language" name="language" class="input cursor-pointer">
                <option value="">{{ ui('language_label', 'Language') }}</option>
                @foreach ($languages as $language)
                    <option value="{{ $language }}" @selected(request('language') === $language)>{{ $language }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="home-cinema">{{ ui('cinemas', 'Cinemas') }}</label>
            <select id="home-cinema" name="cinema" class="input cursor-pointer">
                <option value="">{{ ui('cinemas', 'Cinemas') }}</option>
                @foreach ($cinemas as $cinema)
                    <option value="{{ $cinema->id }}" @selected((string) request('cinema') === (string) $cinema->id)>
                        {{ $cinema->localised_name() }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn-primary sm:col-span-2 lg:col-span-1">
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
         Section 7: Choose Your Cinema
    ============================================================ --}}
    @if ($cinemas->isNotEmpty())
        <section class="container-page pt-16">
            <x-section-head
                :kicker="ui('choose_cinema', 'Choose Your Cinema')"
                :title="ui('choose_cinema_sub', 'Find the CineGo branch closest to you.')"
                :href="route('cinemas.index')"
                :label="ui('view_all', 'View all')"
            />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($cinemas as $cinema)
                    <article class="card card-hover flex flex-col p-5">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-cg-red/15 text-cg-red">
                                <x-icon name="armchair" :size="21" />
                            </span>
                            <span class="badge-dark">{{ $cinema->city?->display_name() }}</span>
                        </div>

                        <h3 class="text-lg font-semibold text-cg-white">{{ $cinema->localised_name() }}</h3>

                        <p class="mt-1.5 flex items-start gap-2 text-sm text-cg-muted">
                            <x-icon name="map-pin" :size="15" class="mt-0.5 shrink-0 text-cg-red" />
                            {{ $cinema->localised_address() }}
                        </p>

                        @if ($cinema->facilities)
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach (array_slice($cinema->facilities, 0, 4) as $facility)
                                    <span class="rounded-md border border-cg-border bg-cg-charcoal px-2 py-1 text-[11px] text-cg-muted">
                                        {{ $facility }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-5 flex gap-2 pt-auto">
                            <a href="{{ route('cinemas.show', $cinema) }}" class="btn-outline flex-1 justify-center px-3 py-2 text-xs">
                                {{ ui('view_cinema', 'View Cinema') }}
                            </a>
                            <a href="{{ route('cinemas.show', $cinema) }}#showtimes" class="btn-primary flex-1 justify-center px-3 py-2 text-xs">
                                {{ ui('view_showtimes', 'View Showtimes') }}
                            </a>
                        </div>
                    </article>
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
