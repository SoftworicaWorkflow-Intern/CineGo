@props(['movie', 'showBook' => true, 'showTrailer' => true, 'showReleaseDateOnPoster' => false])

@php
    $detailRoute = route('movies.show', $movie);
    $isComingSoon = $movie->is_coming_soon && ! $movie->is_now_showing;
    $showPosterReleaseDate = $showReleaseDateOnPoster || $isComingSoon;
@endphp

<article {{ $attributes->merge(['class' => 'cg-card group flex h-full flex-col']) }}>
    <div class="cg-poster">
        <a href="{{ $detailRoute }}" class="absolute inset-0">
            <img
                src="{{ $movie->poster_url }}"
                alt="{{ $movie->localised_title() }}"
                loading="lazy"
                width="400"
                height="600"
            >

            <div class="absolute inset-x-0 top-0 flex items-start justify-between p-3">
                @if ($showPosterReleaseDate && $movie->release_date)
                    <span
                        class="badge-dark"
                        aria-label="{{ ui('release_date', 'Release Date') }}: {{ $movie->release_date->format('M j, Y') }}"
                    >
                        {{ $movie->release_date->format('M j, Y') }}
                    </span>
                @elseif ($movie->certificate)
                    <span class="badge-dark">{{ $movie->certificate }}</span>
                @endif
                @if ($movie->is_featured && $movie->is_now_showing)
                    <span class="badge-red absolute left-3 top-3">{{ ui('now_showing', 'Now Showing') }}</span>
                @endif
            </div>
        </a>

        <div class="cg-poster-overlay pointer-events-none">
            <a href="{{ $detailRoute }}" class="btn-primary pointer-events-auto w-full justify-center text-xs">
                {{ ui('view_details', 'View Details') }}
            </a>
            @if ($showTrailer && $movie->trailer_url)
                <button
                    type="button"
                    data-trailer="{{ $movie->trailer_url }}"
                    class="btn-outline pointer-events-auto w-full justify-center px-3 py-2 text-xs"
                >
                    <x-icon name="play" :size="13" /> {{ ui('watch_trailer', 'Watch Trailer') }}
                </button>
            @endif
        </div>
    </div>

    <div class="mt-3.5 flex flex-1 flex-col gap-2">
        <h3 class="min-h-6 line-clamp-1 font-semibold text-cg-white transition group-hover:text-cg-red">
            <a href="{{ $detailRoute }}">{{ $movie->localised_title() }}</a>
        </h3>

        <p class="min-h-5 line-clamp-1 text-xs text-cg-muted">
            {{ $movie->genre_label() }}
            <span class="meta-dot"></span>{{ $movie->duration_label() }}
            <span class="meta-dot"></span>{{ $movie->language }}
        </p>

        <p class="min-h-5 text-xs text-cg-dim">
            {{ $movie->release_date?->format('M j, Y') }}
        </p>

        <div class="mt-auto flex gap-2 pt-1">
            @if ($showBook)
                <a href="{{ $detailRoute }}#showtimes" class="btn-primary flex-1 justify-center px-3 py-2 text-xs">
                    {{ ui('book_tickets', 'Book Tickets') }}
                </a>
            @endif
        </div>
    </div>
</article>
