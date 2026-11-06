@extends('layouts.admin')

@section('title', $movie->exists ? ui('edit_movie', 'Edit movie') : ui('add_movie', 'Add movie'))

@php
    $allGenres = ['Action', 'Comedy', 'Drama', 'Romance', 'Horror', 'Thriller', 'Animation', 'Adventure', 'Family', 'Sci-Fi', 'Fantasy', 'Mystery', 'Biography', 'Documentary', 'Musical', 'War', 'History', 'Sport'];
    $selectedGenres = old('genres', $movie->genres ?? []);
    $languages = ['Hindi', 'English', 'Nepali', 'Korean', 'Chinese'];
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
        enctype="multipart/form-data"
        class="grid gap-6 xl:grid-cols-[1fr_320px]"
    >
        @csrf
        @if ($movie->exists) @method('PUT') @endif

        <div class="space-y-6">
            <section class="card space-y-4 p-5 sm:p-6">
                <h2 class="font-semibold text-cg-white">{{ ui('basics', 'Basics') }}</h2>

                <div>
                    <label class="label" for="title">{{ ui('title', 'Title') }} *</label>
                    <input id="title" type="text" name="title" value="{{ old('title', $movie->title) }}" class="input" required maxlength="160">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
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
                </div>

                <div>
                    <label class="label" for="certificate">{{ ui('certificate', 'Certificate') }}</label>
                    <input id="certificate" type="text" name="certificate" value="{{ old('certificate', $movie->certificate) }}" class="input" maxlength="10" placeholder="UA / A / G">
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
                <h2 class="font-semibold text-cg-white">{{ ui('description', 'Description') }} *</h2>
                <div>
                    <label class="label" for="synopsis">English</label>
                    <textarea id="synopsis" name="synopsis" rows="5" class="input resize-y" required maxlength="4000">{{ old('synopsis', $movie->synopsis) }}</textarea>
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
                    <label class="label" for="poster">{{ ui('poster', 'Poster image') }} {{ $movie->exists ? '' : '*' }}</label>
                    <label for="poster" data-poster-dropzone class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-cg-border bg-cg-charcoal px-4 py-8 text-center transition hover:border-cg-red">
                        <span class="text-sm font-semibold text-cg-white">{{ ui('drop_poster', 'Drop poster image here') }}</span>
                        <span class="mt-1 text-xs text-cg-muted">{{ ui('browse_poster', 'or click to browse') }}</span>
                        <span data-poster-filename class="mt-2 hidden text-xs font-medium text-cg-red"></span>
                    </label>
                    <input id="poster" type="file" name="poster" accept="image/jpeg,image/png,image/webp" class="sr-only" data-poster-input @required(!$movie->exists)>
                    <p class="mt-1.5 text-xs text-cg-dim">{{ ui('poster_hint', 'JPG, PNG or WebP, up to 2 MB.') }}</p>
                    <img
                        src="{{ $movie->poster_url ?? '' }}"
                        alt=""
                        class="{{ $movie->poster_url ? '' : 'hidden' }} mt-2 h-40 w-28 rounded-lg object-cover"
                        data-poster-preview
                    >
                </div>

                <div>
                    <label class="label" for="trailer_url">{{ ui('trailer', 'Trailer embed URL') }}</label>
                    <input id="trailer_url" type="text" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}" class="input" maxlength="300" placeholder="https://www.youtube.com/embed/…">
                </div>
            </section>

            <button type="submit" class="btn-primary btn-lg btn-block">
                <x-icon name="check" :size="17" />
                {{ $movie->exists ? ui('save', 'Save changes') : ui('create', 'Create movie') }}
            </button>
        </aside>
    </form>
@endsection
