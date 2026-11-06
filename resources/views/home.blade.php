@extends('layouts.app')

@section('title', ui('home', 'Home'))

@section('content')
    @if ($featured->isNotEmpty())
        @php($heroMovie = $featured->first())
        @php($heroImage = $heroMovie->banner_url ?: $heroMovie->poster_url)
        <section
            class="cg-home-hero relative min-h-[min(760px,100svh)] w-full overflow-hidden"
            aria-label="Featured movies"
            data-no-scroll-reveal
            data-hero-pagination
        >
            <div
                class="cg-home-hero-art cg-home-hero-art-active"
                aria-hidden="true"
                style="background-image: url('{{ $heroImage }}')"
                data-hero-art
            ></div>
            <div class="cg-home-hero-art" aria-hidden="true" data-hero-art></div>

            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-r from-black/85 via-black/55 to-black/10"></div>
            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-black/55 via-transparent to-black/10"></div>

            <div class="container-page relative z-[2] flex min-h-[min(760px,100svh)] items-center pb-20 pt-24 sm:pb-24">
                <div class="max-w-3xl">
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/25 bg-black/25 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white backdrop-blur-sm">
                        <span class="h-2 w-2 rounded-full bg-cg-red"></span>
                        {{ ui('now_showing', 'Now Showing') }}
                    </p>
                    <h1 class="font-display text-5xl leading-[0.98] text-white sm:text-6xl lg:text-7xl" data-hero-title>
                        {{ $heroMovie->localised_title() }}
                    </h1>
                    <p class="mt-5 max-w-2xl text-sm leading-7 text-white/80 sm:text-base" data-hero-synopsis>
                        {{ $heroMovie->localised_synopsis() }}
                    </p>
                    <div class="mt-5 flex flex-wrap items-center gap-2 text-xs font-medium text-white/80" data-hero-metadata>
                        <span class="rounded-full border border-white/25 bg-black/25 px-3 py-1.5" data-hero-language>{{ $heroMovie->language }}</span>
                        <span class="rounded-full border border-white/25 bg-black/25 px-3 py-1.5" data-hero-duration>{{ $heroMovie->duration_label() }}</span>
                        @if ($heroMovie->certificate)
                            <span class="rounded-full border border-white/25 bg-black/25 px-3 py-1.5">{{ $heroMovie->certificate }}</span>
                        @endif
                    </div>
                    <div class="mt-8 flex flex-wrap gap-3" data-hero-actions>
                        <a
                            href="{{ route('movies.show', $heroMovie) }}"
                            class="btn-primary btn-lg"
                            data-hero-book-link
                            data-hero-book-title="{{ $heroMovie->localised_title() }}"
                        >
                            <x-icon name="ticket" :size="18" />
                            {{ ui('book_tickets', 'Book Tickets') }}
                        </a>
                        <a href="{{ route('movies.index') }}" class="btn-lg rounded-lg border border-white/35 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                            {{ ui('explore_movies', 'Explore Movies') }}
                        </a>
                    </div>
                </div>
            </div>

            <nav class="absolute inset-x-0 bottom-5 z-10 flex items-center justify-center gap-2" aria-label="Featured movies">
                @foreach ($featured as $index => $movie)
                    @php($image = $movie->banner_url ?: $movie->poster_url)
                    <button
                        type="button"
                        class="group flex h-11 items-center px-1"
                        aria-label="{{ ui('show_featured_movie', 'Show featured movie') }}: {{ $movie->localised_title() }}"
                        aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                        data-hero-page
                        data-hero-image="{{ $image }}"
                        data-hero-label="{{ $movie->localised_title() }}"
                        data-hero-title="{{ $movie->localised_title() }}"
                        data-hero-synopsis="{{ $movie->localised_synopsis() }}"
                        data-hero-language="{{ $movie->language }}"
                        data-hero-duration="{{ $movie->duration_label() }}"
                        data-hero-details="true"
                        data-hero-book-url="{{ route('movies.show', $movie) }}"
                        data-hero-book-title="{{ $movie->localised_title() }}"
                    >
                        <span @class([
                            'h-1 rounded-full transition-all duration-300',
                            'w-8 bg-cg-red' => $index === 0,
                            'w-3 bg-white/45 group-hover:bg-white/75' => $index !== 0,
                        ]) data-hero-indicator></span>
                    </button>
                @endforeach
            </nav>
        </section>
    @endif

    @if ($nowShowing->isNotEmpty())
        <section class="container-page pt-14">
            <x-section-head
                :kicker="ui('explore_movies', 'Explore Movies')"
                :title="ui('now_showing_title', 'Now Playing')"
                :subtitle="ui('now_showing_sub', 'Book your favourite movies playing this week.')"
            />

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 lg:gap-5">
                @foreach ($nowShowing as $movie)
                    <x-movie-card :movie="$movie" :show-trailer="false" />
                @endforeach
            </div>
        </section>
    @endif

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

    <section class="relative isolate overflow-hidden bg-[#F8F9FC] py-16 sm:py-20">
        <div class="pointer-events-none absolute -left-24 top-8 h-64 w-64 rounded-full bg-cg-red/5 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 right-0 h-80 w-80 rounded-full bg-cg-red/5 blur-3xl" aria-hidden="true"></div>

        <div class="container-page relative">
            <div class="mb-10 max-w-3xl">
                <p class="section-kicker">{{ ui('why_title', 'Why Choose CineGo?') }}</p>
                <h2 class="text-3xl font-bold leading-tight tracking-tight text-gray-900 sm:text-4xl lg:text-[2.75rem]">
                    {{ ui('why_sub', 'Booking a ticket should take less than a minute.') }}
                </h2>
                <p class="mt-4 max-w-2xl text-base leading-relaxed text-gray-600 sm:text-lg">
                    {{ ui('why_description', 'Everything you need for a smooth and enjoyable movie booking experience.') }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['icon' => 'zap', 'title' => ui('why_1_title', 'Easy online booking'), 'text' => ui('why_1_text', 'Choose your movie and seats in a few simple steps.')],
                    ['icon' => 'shield-check', 'title' => ui('why_2_title', 'Secure payment'), 'text' => ui('why_2_text', 'Pay confidently with eSewa, Khalti or your card.')],
                    ['icon' => 'qr-code', 'title' => ui('why_3_title', 'Instant digital tickets'), 'text' => ui('why_3_text', 'Get your QR ticket as soon as your booking is confirmed.')],
                    ['icon' => 'armchair', 'title' => ui('why_4_title', 'Convenient seat selection'), 'text' => ui('why_4_text', 'View available seats and choose the ones you prefer.')],
                    ['icon' => 'users', 'title' => ui('why_5_title', 'Multiple cinema branches'), 'text' => ui('why_5_text', 'Find convenient CineGo locations across Nepal.')],
                    ['icon' => 'globe', 'title' => ui('why_6_title', 'English & Nepali support'), 'text' => ui('why_6_text', 'Book in the language you prefer: English or Nepali.')],
                ] as $feature)
                    <article class="group flex min-h-48 flex-col items-start gap-5 rounded-[20px] border border-gray-200 bg-white p-6 shadow-[0_8px_24px_rgba(17,24,39,0.05)] transition duration-300 ease-out hover:-translate-y-1.5 hover:border-cg-red/40 hover:shadow-[0_18px_40px_rgba(229,9,20,0.10)] motion-reduce:transition-none motion-reduce:hover:translate-y-0 sm:p-7">
                        <span class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cg-red/15 to-cg-red/5 text-cg-red transition duration-300 group-hover:scale-110 group-hover:shadow-md group-hover:shadow-cg-red/10 motion-reduce:transition-none motion-reduce:group-hover:scale-100">
                            <x-icon :name="$feature['icon']" :size="24" />
                        </span>
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-gray-900">{{ $feature['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $feature['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
