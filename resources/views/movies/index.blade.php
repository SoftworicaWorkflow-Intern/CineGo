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
        <form action="{{ route('movies.index') }}" method="GET" class="card mb-8 grid gap-3 p-4 md:grid-cols-2 lg:grid-cols-4">
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">

            <div class="relative md:col-span-2 lg:col-span-1">
                <x-icon name="search" :size="16" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-cg-red" />
                <label class="sr-only" for="filter-q">{{ ui('search', 'Search') }}</label>
                <input id="filter-q" type="search" name="q" value="{{ request('q') }}" placeholder="{{ ui('search', 'Search') }}…" class="input pl-10">
            </div>

            <label class="sr-only" for="filter-genre">{{ ui('genre', 'Genre') }}</label>
            <select id="filter-genre" name="genre" class="input cursor-pointer">
                <option value="">{{ ui('genre', 'Genre') }}</option>
                @foreach ($allGenres as $genre)
                    <option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="filter-language">{{ ui('language_label', 'Language') }}</label>
            <select id="filter-language" name="language" class="input cursor-pointer">
                <option value="">{{ ui('language_label', 'Language') }}</option>
                @foreach ($languages as $language)
                    <option value="{{ $language }}" @selected(request('language') === $language)>{{ $language }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="filter-year">{{ ui('year', 'Year') }}</label>
            <select id="filter-year" name="year" class="input cursor-pointer">
                <option value="">{{ ui('year', 'Year') }}</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="filter-cinema">{{ ui('cinemas', 'Cinemas') }}</label>
            <select id="filter-cinema" name="cinema" class="input cursor-pointer">
                <option value="">{{ ui('cinemas', 'Cinemas') }}</option>
                @foreach ($cinemas as $cinema)
                    <option value="{{ $cinema->id }}" @selected((string) request('cinema') === (string) $cinema->id)>
                        {{ $cinema->localised_name() }}
                    </option>
                @endforeach
            </select>

            <label class="sr-only" for="filter-sort">{{ ui('sort_by', 'Sort by') }}</label>
            <select id="filter-sort" name="sort" class="input cursor-pointer">
                <option value="latest" @selected(($sort ?? 'latest') === 'latest')>{{ ui('release_date', 'Release Date') }}</option>
                <option value="title" @selected($sort === 'title')">{{ ui('movies', 'Movies') }} A–Z</option>
                <option value="rating" @selected($sort === 'rating')>{{ ui('rating', 'Rating') }}</option>
                <option value="duration" @selected($sort === 'duration')>{{ ui('duration', 'Duration') }}</option>
            </select>

            <div class="flex gap-2 md:col-span-2 lg:col-span-4">
                <button type="submit" class="btn-primary flex-1">
                    <x-icon name="filter" :size="16" /> {{ ui('apply', 'Apply') }}
                </button>
                <a href="{{ route('movies.index') }}" class="btn-ghost">{{ ui('clear_filters', 'Clear filters') }}</a>
            </div>
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
