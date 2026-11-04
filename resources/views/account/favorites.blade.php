@extends('layouts.app')

@section('title', ui('favorite_movies', 'Favourite movies'))

@section('content')
    <div class="container-page py-10">
        <x-breadcrumbs :items="[['label' => ui('my_account', 'My Account'), 'href' => route('account.dashboard')], ['label' => ui('favorite_movies', 'Favourite movies')]]" />

        <div class="mb-8">
            <p class="section-kicker">{{ ui('my_account', 'My Account') }}</p>
            <h1 class="section-title">{{ ui('favorite_movies', 'Favourite movies') }}</h1>
        </div>

        @include('partials.account-nav')

        @if ($movies->isEmpty())
            <x-empty
                :title="ui('no_favorites', 'No favourite movies yet.')"
                :text="ui('tap_heart_note', 'Tap the heart on any movie page to save it here.')"
                icon="heart"
                :action-label="ui('explore_movies', 'Explore Movies')"
                :action-href="route('movies.index')"
            />
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                @foreach ($movies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $movies->links() }}
            </div>
        @endif
    </div>
@endsection
