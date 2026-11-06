@extends('layouts.admin')

@section('title', $heroSlide->exists ? 'Edit Hero Slide' : 'Add Hero Slide')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.hero-sliders.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> Back to Hero Slider
        </a>
    </div>

    <form
        action="{{ $heroSlide->exists ? route('admin.hero-sliders.update', $heroSlide) : route('admin.hero-sliders.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]"
        data-hero-slide-form
    >
        @csrf
        @if ($heroSlide->exists) @method('PUT') @endif

        <div class="space-y-6">
            <section class="card space-y-4 p-5 sm:p-6">
                <div>
                    <h2 class="font-semibold text-cg-white">Images</h2>
                    <p class="mt-1 text-xs text-cg-dim">JPG, PNG or WebP; maximum 8 MB each. Desktop: at least 1920 × 1080; mobile: at least 1080 × 1350.</p>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ([
                        ['field' => 'desktop_image', 'label' => 'Desktop hero image', 'current' => $heroSlide->desktop_image ? $heroSlide->desktop_image_url() : null, 'recommended' => 'Recommended 1920 × 1080'],
                        ['field' => 'mobile_image', 'label' => 'Mobile hero image', 'current' => $heroSlide->mobile_image ? $heroSlide->mobile_image_url() : null, 'recommended' => 'Recommended 1080 × 1350; falls back to desktop'],
                    ] as $imageField)
                        <div class="space-y-2" data-drop-zone>
                            <label class="label" for="{{ $imageField['field'] }}">{{ $imageField['label'] }} @if ($imageField['field'] === 'desktop_image' && ! $heroSlide->exists) * @endif</label>
                            <label for="{{ $imageField['field'] }}" class="flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-cg-border bg-cg-charcoal p-4 text-center transition hover:border-cg-red" data-drop-target>
                                <x-icon name="download" :size="22" class="text-cg-red" />
                                <span class="mt-2 text-sm font-medium text-cg-white">Drop an image here or browse</span>
                                <span class="mt-1 text-xs text-cg-dim">{{ $imageField['recommended'] }}</span>
                                <input
                                    id="{{ $imageField['field'] }}"
                                    type="file"
                                    name="{{ $imageField['field'] }}"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="sr-only"
                                    data-image-upload="{{ $imageField['field'] }}"
                                    @required($imageField['field'] === 'desktop_image' && ! $heroSlide->exists)
                                >
                            </label>
                            <div class="flex items-center justify-between gap-3">
                                <p class="truncate text-xs text-cg-dim" data-file-name="{{ $imageField['field'] }}">
                                    {{ $imageField['current'] ? 'Current image saved' : 'No image selected' }}
                                </p>
                                @if ($heroSlide->exists)
                                    <label class="flex shrink-0 items-center gap-2 text-xs text-cg-muted">
                                        <input type="checkbox" name="remove_{{ $imageField['field'] }}" value="1" class="h-4 w-4 accent-cg-red" data-remove-image="{{ $imageField['field'] }}">
                                        Remove current
                                    </label>
                                @endif
                            </div>
                            @error($imageField['field'])<p class="text-xs text-cg-red">{{ $message }}</p>@enderror
                            <img
                                src="{{ $imageField['current'] ?? '' }}"
                                alt=""
                                class="{{ $imageField['current'] ? 'block' : 'hidden' }} h-24 w-full rounded-lg object-cover"
                                data-upload-thumbnail="{{ $imageField['field'] }}"
                            >
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="card space-y-4 p-5 sm:p-6">
                <div>
                    <h2 class="font-semibold text-cg-white">Movie information</h2>
                    <p class="mt-1 text-xs text-cg-dim">Optional movie association; all displayed content is saved with the slide.</p>
                </div>

                <div>
                    <label class="label" for="movie_id">Linked movie</label>
                    <select id="movie_id" name="movie_id" class="input cursor-pointer">
                        <option value="">No linked movie</option>
                        @foreach ($movies as $movie)
                            <option value="{{ $movie->id }}" @selected((string) old('movie_id', $heroSlide->movie_id) === (string) $movie->id)>
                                {{ $movie->title }}{{ $movie->title_np ? ' · '.$movie->title_np : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('movie_id')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="movie_title_en">Movie title (English) *</label>
                        <input id="movie_title_en" name="movie_title_en" value="{{ old('movie_title_en', $heroSlide->movie_title_en) }}" class="input" required maxlength="160" data-preview-text="movie-title">
                        @error('movie_title_en')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="movie_title_np">Movie title (Nepali)</label>
                        <input id="movie_title_np" name="movie_title_np" value="{{ old('movie_title_np', $heroSlide->movie_title_np) }}" class="input" maxlength="160" data-preview-text="movie-title" data-preview-locale="np">
                        @error('movie_title_np')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="duration">Duration (minutes)</label>
                        <input id="duration" name="duration" type="number" value="{{ old('duration', $heroSlide->duration) }}" class="input" min="1" max="600" data-preview-text="duration">
                        @error('duration')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="language">Language</label>
                        <input id="language" name="language" value="{{ old('language', $heroSlide->language) }}" class="input" maxlength="40" data-preview-text="language">
                        @error('language')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="release_date">Release date</label>
                        <input id="release_date" name="release_date" type="date" value="{{ old('release_date', $heroSlide->release_date?->format('Y-m-d')) }}" class="input" data-preview-text="release">
                        @error('release_date')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="card space-y-4 p-5 sm:p-6">
                <h2 class="font-semibold text-cg-white">Hero content</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="label_en">Small label (English)</label>
                        <input id="label_en" name="label_en" value="{{ old('label_en', $heroSlide->label_en) }}" class="input" maxlength="60" data-preview-text="label">
                        @error('label_en')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="label_np">Small label (Nepali)</label>
                        <input id="label_np" name="label_np" value="{{ old('label_np', $heroSlide->label_np) }}" class="input" maxlength="60" data-preview-text="label" data-preview-locale="np">
                        @error('label_np')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="title_en">Main heading (English) *</label>
                        <input id="title_en" name="title_en" value="{{ old('title_en', $heroSlide->title_en) }}" class="input" required maxlength="160" data-preview-text="title">
                        @error('title_en')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="title_np">Main heading (Nepali)</label>
                        <input id="title_np" name="title_np" value="{{ old('title_np', $heroSlide->title_np) }}" class="input" maxlength="160" data-preview-text="title" data-preview-locale="np">
                        @error('title_np')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="description_en">Supporting description (English) *</label>
                        <textarea id="description_en" name="description_en" rows="4" class="input resize-y" required maxlength="1200" data-preview-text="description">{{ old('description_en', $heroSlide->description_en) }}</textarea>
                        @error('description_en')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="description_np">Supporting description (Nepali)</label>
                        <textarea id="description_np" name="description_np" rows="4" class="input resize-y" maxlength="1200" data-preview-text="description" data-preview-locale="np">{{ old('description_np', $heroSlide->description_np) }}</textarea>
                        @error('description_np')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="card space-y-4 p-5 sm:p-6">
                <h2 class="font-semibold text-cg-white">Buttons</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="primary_button_text_en">Primary button (English) *</label>
                        <input id="primary_button_text_en" name="primary_button_text_en" value="{{ old('primary_button_text_en', $heroSlide->primary_button_text_en) }}" class="input" required maxlength="60" data-preview-text="primary">
                        @error('primary_button_text_en')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="primary_button_text_np">Primary button (Nepali)</label>
                        <input id="primary_button_text_np" name="primary_button_text_np" value="{{ old('primary_button_text_np', $heroSlide->primary_button_text_np) }}" class="input" maxlength="60" data-preview-text="primary" data-preview-locale="np">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="primary_button_url">Primary button URL *</label>
                        <input id="primary_button_url" name="primary_button_url" value="{{ old('primary_button_url', $heroSlide->primary_button_url) }}" class="input" required maxlength="500" placeholder="/movies/example">
                        @error('primary_button_url')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="secondary_button_text_en">Secondary button (English)</label>
                        <input id="secondary_button_text_en" name="secondary_button_text_en" value="{{ old('secondary_button_text_en', $heroSlide->secondary_button_text_en) }}" class="input" maxlength="60" data-preview-text="secondary">
                        @error('secondary_button_text_en')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="secondary_button_text_np">Secondary button (Nepali)</label>
                        <input id="secondary_button_text_np" name="secondary_button_text_np" value="{{ old('secondary_button_text_np', $heroSlide->secondary_button_text_np) }}" class="input" maxlength="60" data-preview-text="secondary" data-preview-locale="np">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="secondary_button_url">Secondary button URL</label>
                        <input id="secondary_button_url" name="secondary_button_url" value="{{ old('secondary_button_url', $heroSlide->secondary_button_url) }}" class="input" maxlength="500" placeholder="/movies">
                        @error('secondary_button_url')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card space-y-4 p-5">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="font-semibold text-cg-white">Live preview</h2>
                    <div class="flex flex-wrap gap-2">
                        <div class="flex rounded-lg border border-cg-border p-1" role="group" aria-label="Preview size">
                            <button type="button" class="rounded-md bg-cg-red px-3 py-1.5 text-xs font-medium text-white" data-preview-mode-toggle="desktop" aria-pressed="true">Desktop</button>
                            <button type="button" class="rounded-md px-3 py-1.5 text-xs font-medium text-cg-muted" data-preview-mode-toggle="mobile" aria-pressed="false">Mobile</button>
                        </div>
                        <div class="flex rounded-lg border border-cg-border p-1" role="group" aria-label="Preview language">
                            <button type="button" class="rounded-md bg-cg-red px-3 py-1.5 text-xs font-medium text-white" data-preview-locale-toggle="en" aria-pressed="true">EN</button>
                            <button type="button" class="rounded-md px-3 py-1.5 text-xs font-medium text-cg-muted" data-preview-locale-toggle="np" aria-pressed="false">नेपाली</button>
                        </div>
                    </div>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl bg-cg-charcoal" data-preview-frame="desktop">
                    <img src="{{ $heroSlide->desktop_image ? $heroSlide->desktop_image_url() : '' }}" alt="" class="{{ $heroSlide->desktop_image ? 'block' : 'hidden' }} absolute inset-0 h-full w-full object-cover" data-preview-image="desktop">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/50 to-transparent"></div>
                    <div class="absolute inset-0 flex flex-col justify-end gap-2 p-4 text-white">
                        <span class="text-[9px] font-semibold uppercase tracking-widest" data-preview-value="label">{{ old('label_en', $heroSlide->label_en) ?: 'NOW SHOWING' }}</span>
                        <span class="text-xs font-medium text-white/70" data-preview-value="movie-title">{{ old('movie_title_en', $heroSlide->movie_title_en) ?: 'Movie title' }}</span>
                        <strong class="line-clamp-2 text-lg leading-tight" data-preview-value="title">{{ old('title_en', $heroSlide->title_en) ?: 'Your hero heading' }}</strong>
                        <span class="line-clamp-2 text-[10px] leading-4 text-white/80" data-preview-value="description">{{ old('description_en', $heroSlide->description_en) ?: 'Supporting description appears here.' }}</span>
                        <span class="w-fit rounded-md bg-cg-red px-3 py-1.5 text-[10px] font-semibold" data-preview-value="primary">{{ old('primary_button_text_en', $heroSlide->primary_button_text_en) ?: 'Book Tickets' }}</span>
                        <span class="w-fit rounded-md border border-white/40 px-3 py-1.5 text-[10px] font-semibold" data-preview-value="secondary">{{ old('secondary_button_text_en', $heroSlide->secondary_button_text_en) }}</span>
                        <span class="text-[9px] text-white/70"><span data-preview-value="language">{{ old('language', $heroSlide->language) }}</span> <span data-preview-value="duration">{{ old('duration', $heroSlide->duration) }}</span> <span data-preview-value="release">{{ old('release_date', $heroSlide->release_date?->format('Y-m-d')) }}</span></span>
                    </div>
                </div>
                <div class="mx-auto hidden aspect-[4/5] w-56 overflow-hidden rounded-2xl border border-cg-border bg-cg-charcoal" data-preview-frame="mobile">
                    <div class="relative h-full">
                        <img src="{{ $heroSlide->mobile_image ? $heroSlide->mobile_image_url() : ($heroSlide->desktop_image ? $heroSlide->desktop_image_url() : '') }}" alt="" class="{{ $heroSlide->desktop_image || $heroSlide->mobile_image ? 'block' : 'hidden' }} absolute inset-0 h-full w-full object-cover" data-preview-image="mobile">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 flex flex-col gap-2 p-4 text-white">
                            <span class="text-[9px] font-semibold uppercase tracking-widest" data-preview-value="label">{{ old('label_en', $heroSlide->label_en) ?: 'NOW SHOWING' }}</span>
                            <span class="text-xs font-medium text-white/70" data-preview-value="movie-title">{{ old('movie_title_en', $heroSlide->movie_title_en) ?: 'Movie title' }}</span>
                            <strong class="line-clamp-2 text-lg leading-tight" data-preview-value="title">{{ old('title_en', $heroSlide->title_en) ?: 'Your hero heading' }}</strong>
                            <span class="line-clamp-3 text-[10px] leading-4 text-white/80" data-preview-value="description">{{ old('description_en', $heroSlide->description_en) ?: 'Supporting description appears here.' }}</span>
                            <span class="w-fit rounded-md bg-cg-red px-3 py-1.5 text-[10px] font-semibold" data-preview-value="primary">{{ old('primary_button_text_en', $heroSlide->primary_button_text_en) ?: 'Book Tickets' }}</span>
                            <span class="w-fit rounded-md border border-white/40 px-3 py-1.5 text-[10px] font-semibold" data-preview-value="secondary">{{ old('secondary_button_text_en', $heroSlide->secondary_button_text_en) }}</span>
                            <span class="text-[9px] text-white/70"><span data-preview-value="language">{{ old('language', $heroSlide->language) }}</span> <span data-preview-value="duration">{{ old('duration', $heroSlide->duration) }}</span> <span data-preview-value="release">{{ old('release_date', $heroSlide->release_date?->format('Y-m-d')) }}</span></span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card space-y-4 p-5">
                <h2 class="font-semibold text-cg-white">Display settings</h2>
                <div>
                    <label class="label" for="display_order">Display order</label>
                    <input id="display_order" name="display_order" type="number" min="0" value="{{ old('display_order', $heroSlide->display_order ?? 0) }}" class="input" required>
                    @error('display_order')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="status">Publication status</label>
                    <select id="status" name="status" class="input cursor-pointer" required>
                        <option value="draft" @selected(old('status', $heroSlide->status) === 'draft')>Unpublished</option>
                        <option value="published" @selected(old('status', $heroSlide->status) === 'published')>Published</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label class="label" for="starts_at">Publish starts</label>
                        <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $heroSlide->starts_at?->format('Y-m-d\\TH:i')) }}" class="input">
                        @error('starts_at')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="ends_at">Publish ends</label>
                        <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $heroSlide->ends_at?->format('Y-m-d\\TH:i')) }}" class="input">
                        @error('ends_at')<p class="mt-1 text-xs text-cg-red">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <button type="submit" class="btn-primary btn-lg w-full">
                <x-icon name="check" :size="17" />
                {{ $heroSlide->exists ? 'Save changes' : 'Create hero slide' }}
            </button>
        </aside>
    </form>
@endsection
