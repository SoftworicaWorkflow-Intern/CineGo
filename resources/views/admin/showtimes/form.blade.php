@extends('layouts.admin')

@section('title', $showtime->exists ? ui('edit_showtime', 'Edit showtime') : ui('add_showtime', 'Add showtime'))

@php
    $selectedCinemaId = (string) old('cinema_id', $showtime->cinema_id);
    $selectedHallId = (string) old('hall_id', $showtime->hall_id);
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.showtimes.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> {{ ui('back', 'Back') }}
        </a>
    </div>

    <form
        action="{{ $showtime->exists ? route('admin.showtimes.update', $showtime) : route('admin.showtimes.store') }}"
        method="POST"
        class="max-w-3xl space-y-6"
    >
        @csrf
        @if ($showtime->exists) @method('PUT') @endif

        <section class="card space-y-4 p-5 sm:p-6">
            <h2 class="font-semibold text-cg-white">{{ ui('show_details', 'Show details') }}</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="movie_id">{{ ui('movies', 'Movie') }} *</label>
                    <select id="movie_id" name="movie_id" class="input cursor-pointer" required>
                        <option value="">{{ ui('choose_movie', 'Choose a movie…') }}</option>
                        @foreach ($movies as $movie)
                            <option value="{{ $movie->id }}" @selected((string) old('movie_id', $showtime->movie_id) === (string) $movie->id)>
                                {{ $movie->localised_title() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label" for="cinema_id">{{ ui('cinemas', 'Cinema') }} *</label>
                    <select id="cinema_id" name="cinema_id" class="input cursor-pointer" required>
                        <option value="">{{ ui('choose_cinema', 'Choose a cinema…') }}</option>
                        @foreach ($cinemas as $cinema)
                            <option value="{{ $cinema->id }}" @selected($selectedCinemaId === (string) $cinema->id)>
                                {{ $cinema->localised_name() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label" for="hall_id">{{ ui('screens', 'Screen') }} *</label>
                    <select id="hall_id" name="hall_id" class="input cursor-pointer" required>
                        <option value="">{{ ui('choose_screen', 'Choose a screen…') }}</option>
                        @foreach ($cinemas as $cinema)
                            @foreach ($cinema->halls as $hall)
                                <option
                                    value="{{ $hall->id }}"
                                    data-cinema="{{ $cinema->id }}"
                                    {{ $selectedHallId === (string) $hall->id ? '' : 'hidden' }}
                                    @selected($selectedHallId === (string) $hall->id)
                                >
                                    {{ $cinema->localised_name() }} · {{ $hall->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label" for="format">{{ ui('format', 'Format') }} *</label>
                    <select id="format" name="format" class="input cursor-pointer" required>
                        @foreach (['2D', '3D', 'IMAX', '4DX'] as $format)
                            <option value="{{ $format }}" @selected(old('format', $showtime->format ?? '2D') === $format)>{{ $format }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label" for="show_date">{{ ui('date', 'Date') }} *</label>
                    <input id="show_date" type="date" name="show_date" value="{{ old('show_date', $showtime->show_date?->format('Y-m-d')) }}" class="input" required min="{{ now()->format('Y-m-d') }}">
                </div>

                <div>
                    <label class="label" for="start_time">{{ ui('times', 'Start time') }} *</label>
                    <input id="start_time" type="time" name="start_time" value="{{ old('start_time', \Illuminate\Support\Str::of((string) $showtime->start_time)->substr(0, 5)) }}" class="input" required>
                </div>

                <div class="sm:col-span-2">
                    <label class="label" for="language">{{ ui('language_label', 'Language') }}</label>
                    <input id="language" type="text" name="language" value="{{ old('language', $showtime->language) }}" class="input" maxlength="40" placeholder="{{ ui('follows_movie', 'Follows the movie language') }}">
                </div>
            </div>
        </section>

        <section class="card space-y-4 p-5 sm:p-6">
            <h2 class="font-semibold text-cg-white">{{ ui('ticket_prices', 'Ticket prices (Rs.)') }}</h2>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="label" for="price_normal">{{ ui('normal', 'Normal') }} *</label>
                    <input id="price_normal" type="number" name="price_normal" value="{{ old('price_normal', $showtime->price_normal ?? 450) }}" class="input" required min="0" step="1">
                </div>
                <div>
                    <label class="label" for="price_premium">{{ ui('premium', 'Premium') }} *</label>
                    <input id="price_premium" type="number" name="price_premium" value="{{ old('price_premium', $showtime->price_premium ?? 650) }}" class="input" required min="0" step="1">
                </div>
                <div>
                    <label class="label" for="price_recliner">{{ ui('recliner', 'Recliner') }} *</label>
                    <input id="price_recliner" type="number" name="price_recliner" value="{{ old('price_recliner', $showtime->price_recliner ?? 850) }}" class="input" required min="0" step="1">
                </div>
            </div>
        </section>

        <label class="flex w-fit cursor-pointer items-center gap-3 rounded-xl border border-cg-border bg-cg-charcoal px-4 py-3 transition hover:border-cg-red">
            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded accent-cg-red"
                @checked(old('is_active', $showtime->is_active ?? true))>
            <span class="text-sm font-medium text-cg-white">{{ ui('active', 'Active (bookable on site)') }}</span>
        </label>

        <button type="submit" class="btn-primary btn-lg">
            <x-icon name="check" :size="17" />
            {{ $showtime->exists ? ui('save', 'Save changes') : ui('create', 'Create showtime') }}
        </button>
    </form>

    <script>
        (function () {
            var cinemaSelect = document.getElementById('cinema_id');
            var hallSelect = document.getElementById('hall_id');
            if (!cinemaSelect || !hallSelect) return;

            function syncHalls() {
                var cinemaId = cinemaSelect.value;
                var current = hallSelect.value;

                Array.from(hallSelect.options).forEach(function (option) {
                    if (!option.dataset.cinema) return;
                    option.hidden = option.dataset.cinema !== cinemaId;
                });

                var selected = hallSelect.querySelector('option:not([hidden]):not([value=""])');
                if (!current || hallSelect.selectedOptions[0]?.hidden) {
                    hallSelect.value = selected ? selected.value : '';
                }
            }

            cinemaSelect.addEventListener('change', syncHalls);
            syncHalls();
        })();
    </script>
@endsection
