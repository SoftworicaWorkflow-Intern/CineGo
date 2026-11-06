@extends('layouts.admin')

@section('title', 'Preview Hero Banner')

@section('content')
    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('admin.hero-sliders.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> Back to Hero Banners
        </a>
        <a href="{{ route('admin.hero-sliders.edit', $heroSlide) }}" class="btn-outline px-4 py-2 text-sm">
            <x-icon name="edit" :size="15" /> Edit banner
        </a>
    </div>

    <section class="relative mx-auto flex min-h-[min(650px,75svh)] max-w-6xl items-center overflow-hidden rounded-2xl bg-cg-charcoal p-8 sm:p-12" aria-label="Hero banner preview">
        @if ($heroSlide->desktop_image)
            <img src="{{ $heroSlide->desktop_image_url() }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/55 to-black/10"></div>
        <div class="relative flex max-w-3xl items-center gap-6 text-white">
            @if ($heroSlide->movie?->poster_url)
                <img src="{{ $heroSlide->movie->poster_url }}" alt="" class="hidden h-64 w-44 shrink-0 rounded-xl object-cover shadow-2xl sm:block">
            @endif
            @if ($heroSlide->movie)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cg-red">{{ ui('now_showing', 'Now Showing') }}</p>
                    <h2 class="mt-3 text-4xl font-bold sm:text-5xl">{{ $heroSlide->movie->localised_title() }}</h2>
                    <p class="mt-3 text-sm text-white/75">
                        {{ $heroSlide->movie->genre_label() }}
                        · {{ $heroSlide->movie->duration_label() }}
                        · {{ $heroSlide->movie->language }}
                        @if ($heroSlide->movie->certificate) · {{ $heroSlide->movie->certificate }} @endif
                    </p>
                    @if ($heroSlide->movie->release_date)
                        <p class="mt-2 text-sm text-white/70">{{ $heroSlide->movie->release_date->format('M j, Y') }}</p>
                    @endif
                    <p class="mt-5 max-w-xl text-sm leading-relaxed text-white/80">{{ $heroSlide->movie->localised_synopsis() }}</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('movies.show', $heroSlide->movie) }}" class="btn-primary">Book Tickets</a>
                        @if ($heroSlide->movie->valid_trailer_url())
                            <a href="{{ $heroSlide->movie->valid_trailer_url() }}" target="_blank" rel="noopener noreferrer" class="btn-outline">Watch Trailer</a>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-white">The movie linked to this banner is no longer available.</p>
            @endif
        </div>
    </section>
@endsection
