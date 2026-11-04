@extends('layouts.app')

@section('title', ui('all_movies', 'All Movies'))

@php
    $tabs = [
        'all' => ui('all_movies', 'All Movies'),
        'now_showing' => ui('now_showing', 'Now Showing'),
        'coming_soon' => ui('coming_soon', 'Coming Soon'),
        'popular' => ui('popular', 'Popular'),
        'top_rated' => ui('top_rated', 'Top Rated'),
    ];

    $currentQuery = request()->query();
@endphp

@section('content')
    <div class="container-page pt-10">
        <x-breadcrumbs :items="[['label' => ui('movies', 'Movies')]]" />

        <div class="mb-8">
            <p class="section-kicker">{{ ui('explore_movies', 'Explore Movies') }}</p>
            <h1 class="section-title">{{ ui('all_movies', 'All Movies') }}</h1>
        </div>

        {{-- Tabs --}}
        <div class="mb-6 flex flex-wrap gap-2 border-b border-cg-border-soft pb-0">
            @foreach ($tabs as $key => $label)
                <a
                    href="{{ route('movies.index', array_merge($currentQuery, ['tab' => $key])) }}"
                    class="rounded-t-lg border-b-2 px-4 py-3 text-sm font-semibold transition
                        {{ ($tab ?? 'all') === $key
                            ? 'border-cg-red text-cg-white'
                            : 'border-transparent text-cg-muted hover:text-cg-white' }}"
                >{{ $label }}</a>
            @endforeach
        </div>

        {{-- Filters --}}
        <form action="{{ route('movies.index') }}" method="GET" class="card mb-8 grid gap-3 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)_minmax(0,1.2fr)_minmax(0,1.2fr)_auto_auto]">
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">

            <div class="flex h-12 min-w-0 items-center gap-3 rounded-lg border border-cg-border bg-white px-3.5 transition focus-within:border-cg-red focus-within:ring-2 focus-within:ring-cg-red/30">
                <x-icon name="search" :size="17" class="shrink-0 text-cg-red" />
                <label class="sr-only" for="filter-q">{{ ui('search_movies', 'Search movies') }}</label>
                <input id="filter-q" type="search" name="q" value="{{ request('q') }}" placeholder="{{ ui('search_movies', 'Search movies') }}" class="h-full min-h-0 w-full min-w-0 border-0 bg-transparent p-0 text-sm text-cg-white placeholder:text-cg-dim focus:border-transparent focus:outline-none focus:ring-0">
            </div>

            <label class="sr-only" for="filter-genre">{{ ui('genre', 'Genre') }}</label>
            <select id="filter-genre" name="genre" class="input h-12 min-h-12 cursor-pointer py-2.5">
                <option value="">{{ ui('genre', 'Genre') }}</option>
                @foreach ($allGenres as $genre)
                    <option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>
                @endforeach
            </select>

            <div>
                <label class="sr-only" for="filter-date">{{ ui('select_date', 'Select date') }}</label>
                <input id="filter-date" type="date" name="show_date" value="{{ request('show_date') }}" aria-label="{{ ui('select_date', 'Select date') }}" class="input h-12 min-h-12 py-2.5">
            </div>

            <div>
                <label class="sr-only" for="filter-time">{{ ui('show_time', 'Show time') }}</label>
                <input id="filter-time" type="time" name="show_time" value="{{ request('show_time') }}" aria-label="{{ ui('show_time', 'Show time') }}" class="input h-12 min-h-12 py-2.5">
            </div>

            <button type="submit" class="btn-primary h-12 whitespace-nowrap px-5">
                    <x-icon name="search" :size="16" /> {{ ui('search', 'Search') }}
            </button>
            <a href="{{ route('movies.index') }}" class="btn-ghost h-12 whitespace-nowrap px-4">{{ ui('clear_filters', 'Clear filters') }}</a>
        </form>

        <p class="mb-5 text-sm text-cg-muted">
            <span class="font-semibold text-cg-white">{{ $movies->total() }}</span> {{ ui('results', 'movies found') }}
        </p>

        @if ($movies->isEmpty())
            <x-empty
                :title="ui('no_results', 'Nothing matched your search.')"
                :text="ui('try_again', 'Please try again.')"
                icon="search"
                :action-label="ui('clear_filters', 'Clear filters')"
                :action-href="route('movies.index')"
            />
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                @foreach ($movies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $movies->links() }}
            </div>
        @endif
    </div>
@endsection
