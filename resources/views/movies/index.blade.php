@extends('layouts.app')

@section('title', ui('all_movies', 'All Movies'))

@php
    $tabs = [
        'all' => ui('all_movies', 'All Movies'),
        'now_showing' => ui('now_showing', 'Now Showing'),
        'coming_soon' => ui('coming_soon', 'Coming Soon'),
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
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 lg:gap-5">
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
