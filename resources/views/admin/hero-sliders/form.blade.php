@extends('layouts.admin')

@section('title', $heroSlide->exists ? 'Edit Hero Banner' : 'Add Hero Banner')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.hero-sliders.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> Back to Hero Banners
        </a>
    </div>

    @php($selectedMovie = $heroSlide->movie)

    <form
        action="{{ $heroSlide->exists ? route('admin.hero-sliders.update', $heroSlide) : route('admin.hero-sliders.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]"
        data-hero-slide-form
        data-locale="{{ app()->getLocale() }}"
    >
        @csrf
        @if ($heroSlide->exists) @method('PUT') @endif

        <div class="space-y-6">
            <section class="card space-y-4 p-5 sm:p-6">
                <div>
                    <h2 class="font-semibold text-cg-white">Movie and banner image</h2>
                    <p class="mt-1 text-xs text-cg-dim">Movie information is always read from the Movies module.</p>
                </div>

                <div>
                    <label class="label" for="movie_id">Select Movie *</label>
                    <select id="movie_id" name="movie_id" class="input cursor-pointer" required data-movie-select>
                        <option value="">Choose a coming soon movie</option>
                        @foreach ($movies as $movie)
                            <option
                                value="{{ $movie->id }}"
                                data-title-en="{{ $movie->title }}"
                                data-title-np="{{ $movie->title_np }}"
                                data-synopsis-en="{{ $movie->synopsis }}"
                                data-synopsis-np="{{ $movie->synopsis_np }}"
                                data-genres="{{ $movie->genre_label() }}"
                                data-duration="{{ $movie->duration_label() }}"
                                data-language="{{ $movie->language }}"
                                data-release-date="{{ $movie->release_date?->format('M j, Y') }}"
                                data-certificate="{{ $movie->certificate }}"
                                data-trailer="{{ $movie->valid_trailer_url() }}"
                                data-movie-url="{{ route('movies.show', $movie) }}"
                                @selected((string) old('movie_id', $heroSlide->movie_id) === (string) $movie->id)
                            >
                                {{ $movie->localised_title() }}{{ $movie->is_active && $movie->is_coming_soon ? '' : ' (currently linked; no longer upcoming)' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-cg-dim">Only active upcoming movies without an existing hero banner are listed.</p>
                    @error('movie_id')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2" data-drop-zone>
                    <h3 class="font-medium text-cg-white">Desktop banner image</h3>
                    <p class="mt-1 text-xs text-cg-dim">JPG, PNG or WebP; maximum 8 MB each.</p>
                    <label for="desktop_image" class="flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-cg-border bg-cg-charcoal p-4 text-center transition hover:border-cg-red" data-drop-target>
                        <x-icon name="download" :size="22" class="text-cg-red" />
                        <span class="mt-2 text-sm font-medium text-cg-white">Drop an image here or browse</span>
                        <input
                            id="desktop_image"
                            type="file"
                            name="desktop_image"
                            accept="image/jpeg,image/png,image/webp"
                            class="sr-only"
                            data-image-upload="desktop_image"
                            @required(! $heroSlide->exists)
                        >
                    </label>
                    <div class="flex items-center justify-between gap-3">
                        <p class="truncate text-xs text-cg-dim" data-file-name="desktop_image">
                            {{ $heroSlide->desktop_image ? 'Current image saved' : 'No image selected' }}
                        </p>
                        @if ($heroSlide->exists)
                            <label class="flex shrink-0 items-center gap-2 text-xs text-cg-muted">
                                <input type="checkbox" name="remove_desktop_image" value="1" class="h-4 w-4 accent-cg-red" data-remove-image="desktop_image">
                                Remove current
                            </label>
                        @endif
                    </div>
                    @error('desktop_image')<p class="text-xs text-cg-red">{{ $message }}</p>@enderror
                    <img
                        src="{{ $heroSlide->desktop_image_url() ?? '' }}"
                        alt=""
                        class="{{ $heroSlide->desktop_image ? 'block' : 'hidden' }} h-24 w-full rounded-lg object-cover"
                        data-upload-thumbnail="desktop_image"
                    >
                </div>

            </section>

        </div>

        <aside class="space-y-6">
            <section class="card space-y-4 p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-semibold text-cg-white">Live banner preview</h2>
                    <div class="flex rounded-lg border border-cg-border p-1" role="group" aria-label="Preview device">
                        <button type="button" class="rounded-md bg-cg-red px-3 py-1.5 text-xs font-medium text-white" aria-pressed="true" data-preview-mode-toggle="desktop">Desktop</button>
                        <button type="button" class="rounded-md px-3 py-1.5 text-xs font-medium text-cg-muted" aria-pressed="false" data-preview-mode-toggle="mobile">Mobile</button>
                    </div>
                </div>
                <div class="relative mx-auto aspect-video w-full overflow-hidden rounded-xl bg-cg-charcoal transition-all" data-preview-frame="desktop" data-movie-preview>
                    <img
                        src="{{ $heroSlide->desktop_image ? $heroSlide->desktop_image_url() : '' }}"
                        alt=""
                        class="{{ $heroSlide->desktop_image ? 'block' : 'hidden' }} absolute inset-0 h-full w-full object-cover"
                        data-preview-image="desktop"
                        data-preview-image-layer
                    >
                    <img
                        src="{{ $heroSlide->mobile_image ? $heroSlide->mobile_image_url() : '' }}"
                        alt=""
                        class="hidden absolute inset-0 h-full w-full object-cover"
                        data-preview-image="mobile"
                        data-preview-image-layer
                    >
                    <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/55 to-black/10"></div>
                    <div class="absolute inset-0 flex items-end gap-3 p-4 sm:items-center sm:gap-4 sm:p-6">
                        <div class="min-w-0 text-white">
                            <p class="text-[9px] font-semibold uppercase tracking-[0.2em] text-cg-red" data-preview-label>
                                {{ $selectedMovie ? ui('now_showing', 'Now Showing') : 'Select a movie' }}
                            </p>
                            <h3 class="mt-1 line-clamp-2 text-lg font-bold sm:text-2xl" data-preview-title>
                                {{ $selectedMovie?->localised_title() ?? 'Movie title' }}
                            </h3>
                            <p class="mt-1 text-[10px] text-white/75 sm:text-xs">
                                <span data-preview-genres>{{ $selectedMovie?->genre_label() }}</span>
                                <span data-preview-meta-separator> · </span>
                                <span data-preview-duration>{{ $selectedMovie?->duration_label() }}</span>
                                <span data-preview-language-separator> · </span>
                                <span data-preview-language>{{ $selectedMovie?->language }}</span>
                                <span data-preview-certificate-separator class="{{ $selectedMovie?->certificate ? '' : 'hidden' }}"> · </span>
                                <span data-preview-certificate>{{ $selectedMovie?->certificate }}</span>
                            </p>
                            <p class="mt-1 text-[10px] text-white/65 sm:text-xs" data-preview-release-date>
                                {{ $selectedMovie?->release_date?->format('M j, Y') }}
                            </p>
                            <p class="mt-2 line-clamp-2 max-w-md text-[10px] leading-relaxed text-white/80 sm:text-xs" data-preview-synopsis>
                                {{ $selectedMovie?->localised_synopsis() }}
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ $selectedMovie ? route('movies.show', $selectedMovie) : '#' }}" class="rounded-md bg-cg-red px-3 py-1.5 text-[10px] font-semibold text-white" data-preview-book>
                                    Book Tickets
                                </a>
                                <a
                                    href="{{ $selectedMovie?->valid_trailer_url() ?? '#' }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="{{ $selectedMovie?->valid_trailer_url() ? 'inline-flex' : 'hidden' }} items-center gap-1 rounded-md border border-white/40 px-3 py-1.5 text-[10px] font-semibold text-white"
                                    data-preview-trailer
                                >
                                    <x-icon name="play" :size="11" /> Watch Trailer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <button type="submit" class="btn-primary btn-lg w-full">
                <x-icon name="check" :size="17" />
                {{ $heroSlide->exists ? 'Save banner' : 'Create banner' }}
            </button>
        </aside>
    </form>
@endsection
