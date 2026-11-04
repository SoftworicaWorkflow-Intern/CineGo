@extends('layouts.app')

@section('title', $movie->localised_title())
@section('meta', \Illuminate\Support\Str::limit(strip_tags($movie->localised_synopsis()), 155))

@php($dateKeys = $groupedShowtimes->keys()->values())
@php($firstShowtime = $groupedShowtimes->isEmpty() ? null : $groupedShowtimes->first()->sortBy('start_time')->first())

@section('content')
    {{-- Backdrop --}}
    <div class="relative overflow-hidden border-b border-cg-border-soft">
        <img
            src="{{ $movie->banner_url ?? $movie->poster_url }}"
            alt=""
            class="absolute inset-0 h-full w-full object-cover opacity-30"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-cg-black via-cg-black/85 to-cg-black/50"></div>

        <div class="container-page relative pb-10 pt-8">
            <x-breadcrumbs :items="[['label' => ui('movies', 'Movies'), 'href' => route('movies.index')], ['label' => $movie->localised_title()]]" />

            <div class="flex flex-col gap-7 md:flex-row">
                {{-- Poster --}}
                <div class="w-44 shrink-0 sm:w-56">
                    <div class="cg-poster !aspect-[2/3]">
                        <img src="{{ $movie->poster_url }}" alt="{{ $movie->localised_title() }}" width="400" height="600">
                    </div>
                </div>

                {{-- Info --}}
                <div class="min-w-0 flex-1">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="badge-red">{{ $movie->genre_label() }}</span>
                        @if ($movie->is_now_showing)
                            <span class="badge-green">{{ ui('now_showing', 'Now Showing') }}</span>
                        @elseif ($movie->is_coming_soon)
                            <span class="badge-amber">{{ ui('coming_soon', 'Coming Soon') }}</span>
                        @endif
                        @if ($movie->certificate)
                            <span class="badge-dark">{{ $movie->certificate }}</span>
                        @endif
                    </div>

                    <h1 class="font-display text-4xl leading-tight tracking-wide text-cg-white sm:text-5xl">
                        {{ $movie->localised_title() }}
                    </h1>

                    <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-cg-muted">
                        @if ($movie->rating)
                            <span class="inline-flex items-center gap-1.5 font-semibold text-cg-white">
                                <x-icon name="star" :size="16" class="cg-star" />
                                {{ number_format($movie->rating, 1) }} <span class="font-normal text-cg-dim">/ 10</span>
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5"><x-icon name="clock" :size="15" class="text-cg-red" />{{ $movie->duration_label() }}</span>
                        <span class="inline-flex items-center gap-1.5"><x-icon name="globe" :size="15" class="text-cg-red" />{{ $movie->language }}</span>
                        <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" :size="15" class="text-cg-red" />{{ $movie->release_date?->format('M j, Y') }}</span>
                        @if ($movie->rating)
                            <span class="inline-flex items-center gap-1.5"><x-icon name="star" :size="15" class="text-cg-red" />{{ $reviewCount }} {{ ui('rating', 'Rating') }}</span>
                        @endif
                    </div>

                    <p class="mt-5 max-w-3xl leading-relaxed text-cg-muted">{{ $movie->localised_synopsis() }}</p>

                    <dl class="mt-5 grid max-w-2xl gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                        @if ($movie->director)
                            <div class="flex gap-2">
                                <dt class="text-cg-dim">{{ ui('director', 'Director') }}:</dt>
                                <dd class="text-cg-white">{{ $movie->director }}</dd>
                            </div>
                        @endif
                        @if ($movie->cast)
                            <div class="flex gap-2">
                                <dt class="text-cg-dim">{{ ui('cast', 'Cast') }}:</dt>
                                <dd class="text-cg-white">{{ $movie->cast }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="#showtimes" class="btn-primary btn-lg">
                            <x-icon name="ticket" :size="18" /> {{ ui('book_tickets', 'Book Tickets') }}
                        </a>
                        @if ($movie->trailer_url)
                            <button type="button" data-trailer="{{ $movie->trailer_url }}" class="btn-outline btn-lg">
                                <x-icon name="play" :size="17" /> {{ ui('watch_trailer', 'Watch Trailer') }}
                            </button>
                        @endif
                        @auth
                            <form action="{{ route('movies.favorite', $movie) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-outline btn-lg {{ $isFavorite ? 'border-cg-red text-cg-red' : '' }}">
                                    <x-icon name="heart" :size="17" class="{{ $isFavorite ? 'fill-current' : '' }}" />
                                    {{ $isFavorite ? 'Saved' : ui('favorite_movies', 'Favourite') }}
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-page grid gap-10 pt-12 lg:grid-cols-[1fr_340px]">
        <div class="min-w-0 space-y-12">
            {{-- ===================== Showtimes ===================== --}}
            <section id="showtimes" class="scroll-mt-24">
                <x-section-head
                    :kicker="ui('step_select_showtime', 'Showtime')"
                    :title="ui('showtimes_for', 'Showtimes')"
                />

                @if ($dateKeys->isEmpty())
                    <x-empty
                        :title="ui('no_showtimes', 'No showtimes available for this selection.')"
                        :text="ui('showtimes_coming_soon', 'Please check back soon for upcoming showtimes.')"
                        icon="calendar"
                    />
                @else
                    {{-- Date selector --}}
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
                        @php($dayShowtimes = $groupedShowtimes[$dateKey]->sortBy('start_time'))
                        <div
                            data-date-panel="{{ $dateKey }}"
                            data-booking-url="{{ route('booking.seats', $dayShowtimes->first()) }}"
                            class="{{ $index === 0 ? '' : 'hidden' }}"
                        >
                            <div class="card p-4 sm:p-5">
                                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4">
                                    @foreach ($dayShowtimes as $showtime)
                                        <a
                                            href="{{ route('booking.seats', $showtime) }}"
                                            class="group flex flex-col items-center rounded-lg border border-cg-border bg-cg-charcoal px-3 py-2.5 transition hover:border-cg-red hover:bg-cg-red/10"
                                        >
                                            <span class="text-sm font-bold text-cg-white">{{ $showtime->start_time_label() }}</span>
                                            <span class="mt-0.5 text-[11px] text-cg-dim">
                                                {{ $showtime->format }} · {{ $showtime->language }} · Rs. {{ number_format($showtime->price_normal) }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </section>

            {{-- ===================== Reviews ===================== --}}
            <section id="reviews">
                <x-section-head
                    :kicker="ui('rating', 'Rating')"
                    :title="ui('reviews', 'Reviews')"
                />

                <div class="mb-6 flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-3">
                        <span class="font-display text-5xl text-cg-white">
                            {{ $averageRating > 0 ? number_format($averageRating, 1) : '—' }}
                        </span>
                        <span>
                            <span class="flex gap-0.5">
                                @for ($star = 1; $star <= 5; $star++)
                                    <x-icon name="star" :size="16" class="{{ $star <= round($averageRating) ? 'cg-star' : 'text-cg-border' }}" />
                                @endfor
                            </span>
                            <span class="text-xs text-cg-dim">{{ $reviewCount }} {{ ui('reviews_count', 'reviews') }}</span>
                        </span>
                    </div>

                    @auth
                        <a href="#write-review" class="btn-outline ml-auto px-4 py-2 text-sm">
                            <x-icon name="edit" :size="15" /> {{ ui('write_review', 'Write a review') }}
                        </a>
                    @endauth
                </div>

                @auth
                    <form id="write-review" action="{{ route('movies.review', $movie) }}" method="POST" class="card mb-8 space-y-4 p-5">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="label" for="review-rating">{{ ui('your_rating', 'Your rating') }} *</label>
                                <select id="review-rating" name="rating" class="input cursor-pointer" required>
                                    @for ($star = 5; $star >= 1; $star--)
                                        <option value="{{ $star }}" @selected(old('rating', $userReview?->rating) == $star)>
                                            {{ $star }} ★
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="label" for="review-title">{{ ui('review_title', 'Title (optional)') }}</label>
                                <input id="review-title" type="text" name="title" value="{{ old('title', $userReview?->title) }}" class="input" maxlength="80">
                            </div>
                        </div>
                        <div>
                            <label class="label" for="review-body">{{ ui('message', 'Message') }}</label>
                            <textarea id="review-body" name="body" rows="3" class="input resize-y" maxlength="2000">{{ old('body', $userReview?->body) }}</textarea>
                        </div>
                        <button type="submit" class="btn-primary">{{ ui('send_message', 'Submit') }}</button>
                    </form>
                @else
                    <p class="card mb-8 p-5 text-sm text-cg-muted">
                        {{ ui('login_to_review', 'Log in to write a review.') }}
                        <a href="{{ route('login') }}" class="font-semibold text-cg-red hover:underline">{{ ui('login', 'Login') }}</a>
                    </p>
                @endauth

                <div class="space-y-4">
                    @forelse ($reviews as $review)
                        <article class="card p-5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-cg-red/20 text-sm font-bold text-cg-red">
                                        {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
                                    </span>
                                    <span>
                                        <span class="block text-sm font-semibold text-cg-white">{{ $review->user?->name ?? 'Guest' }}</span>
                                        <span class="flex gap-0.5">
                                            @for ($star = 1; $star <= 5; $star++)
                                                <x-icon name="star" :size="12" class="{{ $star <= $review->rating ? 'cg-star' : 'text-cg-border' }}" />
                                            @endfor
                                        </span>
                                    </span>
                                </div>
                                <span class="text-xs text-cg-dim">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if ($review->title)
                                <h4 class="mt-3 font-semibold text-cg-white">{{ $review->title }}</h4>
                            @endif
                            @if ($review->body)
                                <p class="mt-1.5 text-sm leading-relaxed text-cg-muted">{{ $review->body }}</p>
                            @endif
                        </article>
                    @empty
                        <x-empty :title="ui('no_reviews', 'No reviews yet.')" :text="ui('be_first', 'Be the first to review this movie.')" icon="star" />
                    @endforelse
                </div>
            </section>
        </div>

        {{-- ===================== Sidebar ===================== --}}
        <aside class="space-y-6">
            <div class="card sticky top-24 space-y-4 p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-cg-red/15 text-cg-red">
                        <x-icon name="ticket" :size="24" />
                    </span>
                    <span>
                        <span class="block font-semibold text-cg-white">{{ ui('book_tickets', 'Book Tickets') }}</span>
                        <span class="text-xs text-cg-muted">{{ ui('choose_date', 'Choose a date') }} → {{ ui('choose_showtime', 'Choose a showtime') }}</span>
                    </span>
                </div>

                @if ($dateKeys->isNotEmpty())
                    <div class="rounded-lg border border-cg-border bg-cg-charcoal p-3 text-sm text-cg-muted">
                        <p class="mb-1.5 text-xs uppercase tracking-wide text-cg-dim">{{ ui('dates', 'Dates') }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($dateKeys as $dateKey)
                                <button
                                    type="button"
                                    data-date-chip="{{ $dateKey }}"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                    class="rounded-md border border-cg-border px-2.5 py-1.5 text-xs text-cg-white transition hover:border-cg-red"
                                >{{ \Illuminate\Support\Carbon::parse($dateKey)->format('M j') }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($firstShowtime)
                    <a data-booking-link href="{{ route('booking.seats', $firstShowtime) }}" class="btn-primary btn-block">
                        {{ ui('proceed_to_seats', 'Choose your seats') }}
                    </a>
                @else
                    <button type="button" class="btn-primary btn-block cursor-not-allowed opacity-60" disabled>
                        {{ ui('proceed_to_seats', 'Choose your seats') }}
                    </button>
                @endif

                <p class="flex items-start gap-2 text-xs text-cg-dim">
                    <x-icon name="info" :size="14" class="mt-0.5 shrink-0 text-cg-red" />
                    {{ ui('demo_payment_note', 'Pay with eSewa, Khalti or card — your seat is confirmed instantly.') }}
                </p>
            </div>

            {{-- Similar movies --}}
            @if ($similar->isNotEmpty())
                <div class="card p-5">
                    <h3 class="mb-4 font-semibold text-cg-white">{{ ui('similar_movies', 'Similar movies') }}</h3>
                    <div class="space-y-3">
                        @foreach ($similar as $similarMovie)
                            <a href="{{ route('movies.show', $similarMovie) }}" class="flex items-center gap-3 rounded-lg p-1.5 transition hover:bg-cg-charcoal">
                                <img src="{{ $similarMovie->poster_url }}" alt="{{ $similarMovie->localised_title() }}" class="h-16 w-11 rounded-md object-cover" loading="lazy">
                                <span class="min-w-0">
                                    <span class="line-clamp-1 block text-sm font-medium text-cg-white">{{ $similarMovie->localised_title() }}</span>
                                    <span class="block text-xs text-cg-dim">{{ $similarMovie->genre_label() }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>

    <script>
        (function () {
            var chips = document.querySelectorAll('[data-date-chip]');
            var panels = document.querySelectorAll('[data-date-panel]');
            var bookingLink = document.querySelector('[data-booking-link]');
            if (!chips.length) return;

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var date = chip.getAttribute('data-date-chip');

                    panels.forEach(function (panel) {
                        panel.classList.toggle('hidden', panel.getAttribute('data-date-panel') !== date);
                    });

                    var activePanel = Array.from(panels).find(function (panel) {
                        return panel.getAttribute('data-date-panel') === date;
                    });
                    if (bookingLink && activePanel) {
                        bookingLink.href = activePanel.getAttribute('data-booking-url');
                    }

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
