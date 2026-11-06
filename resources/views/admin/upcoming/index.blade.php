@extends('layouts.admin')

@section('title', ui('upcoming', 'Upcoming'))

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-cg-muted">{{ ui('upcoming_subtitle', 'Manage movies scheduled for future release.') }}</p>
        </div>
        <a href="{{ route('admin.movies.create') }}" class="btn-primary">
            <x-icon name="plus" :size="17" /> {{ ui('add_movie', 'Add movie') }}
        </a>
    </div>

    <form action="{{ route('admin.upcoming.index') }}" method="GET" class="mb-5 flex w-full items-center justify-center gap-2 sm:w-auto">
        <div class="relative flex h-9 min-w-0 flex-1 items-center sm:flex-none">
            <x-icon name="search" :size="14" class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-cg-red" />
            <input type="search" name="q" value="{{ $term }}" placeholder="{{ ui('search', 'Search') }}…" class="input h-9 min-h-9 min-w-0 py-2 pl-8 text-xs sm:w-56">
        </div>
        <button type="submit" class="btn-outline h-9 min-h-9 px-3 text-xs">{{ ui('search', 'Search') }}</button>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($movies as $movie)
            <article class="card flex gap-4 p-4">
                <img src="{{ $movie->poster_url }}" alt="" class="h-32 w-22 rounded-lg object-cover" loading="lazy">
                <div class="min-w-0 flex-1">
                    <span class="badge-amber">{{ ui('coming_soon', 'Coming Soon') }}</span>
                    <h2 class="mt-3 truncate font-semibold text-cg-white">{{ $movie->localised_title() }}</h2>
                    <p class="mt-1 text-sm text-cg-muted">{{ $movie->release_date?->format('M j, Y') }}</p>
                    <p class="mt-1 text-xs text-cg-dim">{{ $movie->genre_label() }} · {{ $movie->duration_label() }}</p>
                    <a href="{{ route('admin.movies.edit', $movie) }}" class="mt-4 inline-flex text-xs font-semibold text-cg-red hover:underline">
                        {{ ui('edit_movie', 'Edit movie') }} <span class="ml-1" aria-hidden="true">→</span>
                    </a>
                </div>
            </article>
        @empty
            <div class="card px-5 py-10 text-center text-cg-muted sm:col-span-2 xl:col-span-3">
                {{ ui('no_upcoming_movies', 'No upcoming movies found.') }}
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $movies->links() }}
    </div>
@endsection
