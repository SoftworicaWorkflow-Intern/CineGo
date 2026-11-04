@props(['movie', 'showBook' => true])

@php
    $detailRoute = route('movies.show', $movie);
    $isComingSoon = $movie->is_coming_soon && ! $movie->is_now_showing;
@endphp

<article {{ $attributes->merge(['class' => 'cg-card group']) }}>
    <a href="{{ $detailRoute }}" class="cg-poster">
        <img
            src="{{ $movie->poster_url }}"
            alt="{{ $movie->localised_title() }}"
            loading="lazy"
            width="400"
            height="600"
        >

        <div class="absolute inset-x-0 top-0 flex items-start justify-between p-3">
            @if ($movie->certificate)
                <span class="badge-dark">{{ $movie->certificate }}</span>
            @endif
            @if ($movie->rating)
                <span class="badge-dark">
                    <x-icon name="star" :size="12" class="cg-star" />
                    {{ number_format($movie->rating, 1) }}
                </span>
            @endif
            @if ($movie->is_featured && $movie->is_now_showing)
                <span class="badge-red absolute left-3 top-3">{{ ui('now_showing', 'Now Showing') }}</span>
            @endif
        </div>

        <div class="cg-poster-overlay">
            <span class="btn-primary w-full justify-center text-xs">
                {{ ui('view_details', 'View Details') }}
            </span>
        </div>
    </a>

    <div class="mt-3.5 space-y-2">
        <h3 class="line-clamp-1 font-semibold text-cg-white transition group-hover:text-cg-red">
            <a href="{{ $detailRoute }}">{{ $movie->localised_title() }}</a>
        </h3>

        <p class="line-clamp-1 text-xs text-cg-muted">
            {{ $movie->genre_label() }}
            <span class="meta-dot"></span>{{ $movie->duration_label() }}
            <span class="meta-dot"></span>{{ $movie->language }}
        </p>

        <p class="text-xs text-cg-dim">
            @if ($isComingSoon)
                {{ ui('release_date', 'Release Date') }}: {{ $movie->release_date?->format('M j, Y') }}
            @else
                {{ $movie->release_date?->format('M j, Y') }}
            @endif
        </p>

        <div class="flex gap-2 pt-1">
            @if ($showBook && ! $isComingSoon)
                <a href="{{ $detailRoute }}#showtimes" class="btn-primary flex-1 justify-center px-3 py-2 text-xs">
                    {{ ui('book_tickets', 'Book Tickets') }}
                </a>
            @endif
            @if ($movie->trailer_url)
                <button
                    type="button"
                    data-trailer="{{ $movie->trailer_url }}"
                    class="{{ $showBook && ! $isComingSoon ? 'btn-outline flex-1' : 'btn-outline w-full' }} justify-center px-3 py-2 text-xs"
                >
                    <x-icon name="play" :size="13" /> {{ ui('watch_trailer', 'Watch Trailer') }}
                </button>
            @endif
        </div>
    </div>
</article>
