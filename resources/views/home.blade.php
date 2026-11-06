@extends('layouts.app')

@section('title', ui('home', 'Home'))

@section('content')
    @if ($heroItems->isNotEmpty())
        @php($heroItem = $heroItems->first())
        @php($heroMovie = $heroItem['movie'])
        <section
            class="cg-home-hero relative min-h-[min(760px,100svh)] w-full overflow-hidden"
            aria-label="Featured movies"
            data-no-scroll-reveal
            data-hero-pagination
        >
            <div
                class="cg-home-hero-art cg-home-hero-art-active"
                aria-hidden="true"
                style="--hero-image-desktop: url('{{ $heroItem['desktop_image'] }}'); --hero-image-mobile: url('{{ $heroItem['mobile_image'] ?: $heroItem['desktop_image'] }}')"
                data-hero-art
            ></div>
            <div class="cg-home-hero-art" aria-hidden="true" data-hero-art></div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-black/90 via-black/60 to-black/20" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-black/80 to-transparent" aria-hidden="true"></div>

            <div class="container-page relative z-[1] flex min-h-[min(760px,100svh)] items-center py-16 sm:py-20">
                <div class="flex max-w-4xl items-center gap-5 sm:gap-8" data-hero-content>
                    <img
                        src="{{ $heroMovie->poster_url }}"
                        alt="{{ $heroMovie->localised_title() }}"
                        class="hidden h-64 w-44 shrink-0 rounded-xl object-cover shadow-2xl ring-1 ring-white/15 lg:block"
                        data-hero-poster
                    >
                    <div class="min-w-0 text-white">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-cg-red sm:text-sm" data-hero-label>
                            {{ ui('now_showing', 'Now Showing') }}
                        </p>
                        <h1 class="mt-3 text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl" data-hero-title>
                            {{ $heroMovie->localised_title() }}
                        </h1>
                        <p class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-white/80 sm:text-sm">
                            <span data-hero-genres>{{ $heroMovie->genre_label() }}</span>
                            <span aria-hidden="true">·</span>
                            <span data-hero-duration>{{ $heroMovie->duration_label() }}</span>
                            <span aria-hidden="true">·</span>
                            <span data-hero-language>{{ $heroMovie->language }}</span>
                            <span
                                aria-hidden="true"
                                class="{{ $heroMovie->certificate ? '' : 'hidden' }}"
                                data-hero-certificate-separator
                            >·</span>
                            <span data-hero-certificate>{{ $heroMovie->certificate }}</span>
                        </p>
                        <p class="mt-2 text-xs text-white/70 sm:text-sm">
                            {{ ui('release_date', 'Release date') }}:
                            <span data-hero-release-date>{{ $heroMovie->release_date?->format('M j, Y') }}</span>
                        </p>
                        <p class="mt-4 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base" data-hero-synopsis>
                            {{ $heroMovie->localised_synopsis() }}
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3" data-hero-actions>
                            <a href="{{ route('movies.show', $heroMovie) }}" class="btn-primary btn-lg" data-hero-book>
                                <x-icon name="ticket" :size="17" /> {{ ui('book_tickets', 'Book Tickets') }}
                            </a>
                            <button
                                type="button"
                                class="{{ $heroMovie->valid_trailer_url() ? 'inline-flex' : 'hidden' }} btn-outline btn-lg"
                                data-trailer="{{ $heroMovie->valid_trailer_url() ?? '' }}"
                                data-hero-trailer
                                @disabled(! $heroMovie->valid_trailer_url())
                            >
                                <x-icon name="play" :size="17" /> {{ ui('watch_trailer', 'Watch Trailer') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if ($heroItems->count() > 1)
                <button type="button" class="absolute left-4 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/30 bg-black/35 text-white backdrop-blur transition hover:bg-black/65 sm:left-7 sm:h-12 sm:w-12" aria-label="Previous hero banner" data-hero-prev>
                    <x-icon name="arrow-left" :size="18" />
                </button>
                <button type="button" class="absolute right-4 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/30 bg-black/35 text-white backdrop-blur transition hover:bg-black/65 sm:right-7 sm:h-12 sm:w-12" aria-label="Next hero banner" data-hero-next>
                    <x-icon name="arrow-right" :size="18" />
                </button>
            @endif

            <nav class="absolute inset-x-0 bottom-5 z-10 flex items-center justify-center gap-2" aria-label="Featured movies">
                @foreach ($heroItems as $index => $item)
                    <button
                        type="button"
                        class="group flex h-11 items-center px-1"
                        aria-label="Show hero banner {{ $index + 1 }}"
                        aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                        data-hero-page
                        data-hero-image="{{ $item['desktop_image'] }}"
                        data-hero-mobile-image="{{ $item['mobile_image'] ?: $item['desktop_image'] }}"
                        data-hero-title="{{ $item['movie']->localised_title() }}"
                        data-hero-synopsis="{{ $item['movie']->localised_synopsis() }}"
                        data-hero-genres="{{ $item['movie']->genre_label() }}"
                        data-hero-duration="{{ $item['movie']->duration_label() }}"
                        data-hero-language="{{ $item['movie']->language }}"
                        data-hero-certificate="{{ $item['movie']->certificate }}"
                        data-hero-release-date="{{ $item['movie']->release_date?->format('M j, Y') }}"
                        data-hero-book-url="{{ route('movies.show', $item['movie']) }}"
                        data-hero-trailer-url="{{ $item['movie']->valid_trailer_url() ?? '' }}"
                        data-hero-poster="{{ $item['movie']->poster_url }}"
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
