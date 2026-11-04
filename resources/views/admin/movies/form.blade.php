@extends('layouts.admin')

@section('title', $movie->exists ? ui('edit_movie', 'Edit movie') : ui('add_movie', 'Add movie'))

@php
    $allGenres = ['Action', 'Comedy', 'Drama', 'Romance', 'Horror', 'Thriller', 'Animation', 'Adventure', 'Family', 'Sci-Fi', 'Fantasy', 'Mystery', 'Biography', 'Documentary', 'Musical', 'War', 'History', 'Sport'];
    $selectedGenres = old('genres', $movie->genres ?? []);
    $languages = ['Nepali', 'Hindi', 'English', 'Korean', 'Chinese'];
@endphp

@section('content')
    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('admin.movies.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> {{ ui('back', 'Back') }}
        </a>
        @if ($movie->exists)
            <a href="{{ route('movies.show', $movie) }}" class="btn-outline px-4 py-2 text-sm">
                <x-icon name="eye" :size="15" /> {{ ui('view_details', 'View Details') }}
            </a>
        @endif
    </div>

    <form
        action="{{ $movie->exists ? route('admin.movies.update', $movie) : route('admin.movies.store') }}"
        method="POST"
        class="grid gap-6 xl:grid-cols-[1fr_320px]"
    >
        @csrf
        @if ($movie->exists) @method('PUT') @endif

        <div class="space-y-6">
            <section class="card space-y-4 p-5 sm:p-6">
                <h2 class="font-semibold text-cg-white">{{ ui('basics', 'Basics') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="title">{{ ui('title', 'Title') }} *</label>
                        <input id="title" type="text" name="title" value="{{ old('title', $movie->title) }}" class="input" required maxlength="160">
                    </div>
                    <div>
                        <label class="label" for="title_np">{{ ui('title_np', 'Title (नेपाली)') }}</label>
                        <input id="title_np" type="text" name="title_np" value="{{ old('title_np', $movie->title_np) }}" class="input" maxlength="160">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="label" for="duration_minutes">{{ ui('duration', 'Duration') }} (min) *</label>
                        <input id="duration_minutes" type="number" name="duration_minutes" value="{{ old('duration_minutes', $movie->duration_minutes ?? 120) }}" class="input" required min="1" max="600">
                    </div>
                    <div>
                        <label class="label" for="language">{{ ui('language_label', 'Language') }} *</label>
                        <select id="language" name="language" class="input cursor-pointer" required>
                            @foreach ($languages as $lang)
                                <option value="{{ $lang }}" @selected(old('language', $movie->language) === $lang)>{{ $lang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="release_date">{{ ui('release_date', 'Release date') }} *</label>
                        <input id="release_date" type="date" name="release_date" value="{{ old('release_date', $movie->release_date?->format('Y-m-d')) }}" class="input" required>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="label" for="certificate">{{ ui('certificate', 'Certificate') }}</label>
                        <input id="certificate" type="text" name="certificate" value="{{ old('certificate', $movie->certificate) }}" class="input" maxlength="10" placeholder="UA / A / G">
                    </div>
                    <div>
                        <label class="label" for="rating">{{ ui('rating', 'Rating') }} /10</label>
                        <input id="rating" type="number" name="rating" step="0.1" min="0" max="10" value="{{ old('rating', $movie->rating) }}" class="input" placeholder="7.8">
                    </div>
                    <div>
                        <label class="label" for="sort_order">{{ ui('sort_order', 'Sort order') }}</label>
                        <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', $movie->sort_order ?? 0) }}" class="input">
                    </div>
                </div>
            </section>

            <section class="card space-y-4 p-5 sm:p-6">
                <h2 class="font-semibold text-cg-white">{{ ui('genres', 'Genres') }} *</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($allGenres as $genre)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="genres[]" value="{{ $genre }}" class="peer sr-only"
                                @checked(in_array($genre, $selectedGenres, true))>
                            <span class="inline-block rounded-full border border-cg-border bg-cg-charcoal px-4 py-2 text-sm font-medium text-cg-muted transition hover:border-cg-red peer-checked:border-cg-red peer-checked:bg-cg-red peer-checked:text-white">
                                {{ $genre }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="card space-y-4 p-5 sm:p-6">
                <h2 class="font-semibold text-cg-white">{{ ui('synopsis', 'Storyline') }} *</h2>
                <div>
                    <label class="label" for="synopsis">English</label>
                    <textarea id="synopsis" name="synopsis" rows="5" class="input resize-y" required maxlength="4000">{{ old('synopsis', $movie->synopsis) }}</textarea>
                </div>
                <div>
                    <label class="label" for="synopsis_np">नेपाली</label>
                    <textarea id="synopsis_np" name="synopsis_np" rows="5" class="input resize-y" maxlength="4000">{{ old('synopsis_np', $movie->synopsis_np) }}</textarea>
                </div>
            </section>

            <section class="card space-y-4 p-5 sm:p-6">
                <h2 class="font-semibold text-cg-white">{{ ui('cast_crew', 'Cast & crew') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="director">{{ ui('director', 'Director') }}</label>
                        <input id="director" type="text" name="director" value="{{ old('director', $movie->director) }}" class="input" maxlength="120">
                    </div>
                    <div>
                        <label class="label" for="cast">{{ ui('cast', 'Cast') }}</label>
                        <input id="cast" type="text" name="cast" value="{{ old('cast', $movie->cast) }}" class="input" maxlength="500" placeholder="Actor A, Actor B, …">
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card space-y-4 p-5">
                <h2 class="font-semibold text-cg-white">{{ ui('media', 'Media') }}</h2>

                <div>
                    <label class="label" for="poster_url">{{ ui('poster', 'Poster path') }} *</label>
                    <input id="poster_url" type="text" name="poster_url" value="{{ old('poster_url', $movie->poster_url ?? '/img/posters/poster-1.svg') }}" class="input" required maxlength="300" placeholder="/img/posters/poster-1.svg">
                    @if ($movie->poster_url ?? null)
                        <img src="{{ $movie->poster_url }}" alt="" class="mt-2 h-40 w-28 rounded-lg object-cover">
                    @endif
                </div>

                <div>
                    <label class="label" for="banner_url">{{ ui('banner', 'Banner path') }}</label>
                    <input id="banner_url" type="text" name="banner_url" value="{{ old('banner_url', $movie->banner_url) }}" class="input" maxlength="300" placeholder="/img/banners/banner-1.svg">
                </div>

                <div>
                    <label class="label" for="trailer_url">{{ ui('trailer', 'Trailer embed URL') }}</label>
                    <input id="trailer_url" type="text" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}" class="input" maxlength="300" placeholder="https://www.youtube.com/embed/…">
                </div>
            </section>

            <section class="card space-y-3 p-5">
                <h2 class="font-semibold text-cg-white">{{ ui('visibility', 'Visibility') }}</h2>

                @foreach ([
                    'is_now_showing' => ui('now_showing', 'Now Showing'),
                    'is_coming_soon' => ui('coming_soon', 'Coming Soon'),
                    'is_featured' => ui('featured_hero', 'Featured in hero slider'),
                    'is_active' => ui('active', 'Active (visible on site)'),
                ] as $field => $label)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-cg-border bg-cg-charcoal px-3.5 py-3 transition hover:border-cg-red">
                        <input type="checkbox" name="{{ $field }}" value="1" class="h-4 w-4 rounded border-cg-border bg-cg-charcoal accent-cg-red"
                            @checked(old($field, $movie->{$field} ?? ($field === 'is_active' || $field === 'is_now_showing')))>
                        <span class="text-sm text-cg-white">{{ $label }}</span>
                    </label>
                @endforeach
            </section>

            <button type="submit" class="btn-primary btn-lg btn-block">
                <x-icon name="check" :size="17" />
                {{ $movie->exists ? ui('save', 'Save changes') : ui('create', 'Create movie') }}
            </button>
        </aside>
    </form>
@endsection
