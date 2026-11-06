@extends('layouts.admin')

@section('title', 'Hero Slider')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-cg-muted">Manage the homepage hero and promotional banners.</p>
        </div>
        <a href="{{ route('admin.hero-sliders.create') }}" class="btn-primary">
            <x-icon name="plus" :size="17" /> Add New Slide
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-cg-border-soft px-5 py-4">
            <div>
                <h2 class="font-semibold text-cg-white">Homepage slides</h2>
                <p class="mt-1 text-xs text-cg-dim">Drag rows to change their display order.</p>
            </div>
            <p class="text-xs text-cg-muted" data-reorder-feedback aria-live="polite"></p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm" data-slide-list data-reorder-url="{{ route('admin.hero-sliders.reorder') }}">
                <thead>
                    <tr class="border-b border-cg-border-soft text-left text-xs uppercase tracking-wide text-cg-dim">
                        <th class="w-12 px-4 py-3" scope="col"><span class="sr-only">Reorder</span></th>
                        <th class="px-4 py-3 font-semibold" scope="col">Banner</th>
                        <th class="px-4 py-3 font-semibold" scope="col">Movie / heading</th>
                        <th class="px-4 py-3 font-semibold" scope="col">Schedule</th>
                        <th class="px-4 py-3 font-semibold" scope="col">Status</th>
                        <th class="px-4 py-3 text-right font-semibold" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cg-border-soft">
                    @forelse ($heroSlides as $heroSlide)
                        <tr class="transition hover:bg-cg-charcoal" data-slide-row data-slide-id="{{ $heroSlide->id }}">
                            <td class="px-4 py-4">
                                <button type="button" draggable="true" class="cursor-grab rounded p-2 text-cg-dim hover:text-cg-white active:cursor-grabbing" data-drag-handle aria-label="Drag to reorder {{ $heroSlide->movie_title_en }}">
                                    <x-icon name="grip" :size="16" />
                                </button>
                            </td>
                            <td class="px-4 py-4">
                                @if ($heroSlide->desktop_image)
                                    <img src="{{ $heroSlide->desktop_image_url() }}" alt="" class="h-14 w-24 rounded-md border border-cg-border-soft object-cover" loading="lazy">
                                @else
                                    <div class="flex h-14 w-24 items-center justify-center rounded-md border border-cg-border-soft bg-cg-charcoal text-xs text-cg-dim">No image</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="block font-medium text-cg-white">{{ $heroSlide->movie_title_en }}</span>
                                <span class="mt-1 block max-w-xs truncate text-xs text-cg-dim">{{ $heroSlide->title_en }}</span>
                            </td>
                            <td class="px-4 py-4 text-xs text-cg-muted">
                                <span class="block">Starts: {{ $heroSlide->starts_at?->format('M j, Y H:i') ?? 'Immediately' }}</span>
                                <span class="mt-1 block">Ends: {{ $heroSlide->ends_at?->format('M j, Y H:i') ?? 'No end date' }}</span>
                            </td>
                            <td class="px-4 py-4">
                                @if ($heroSlide->status === \App\Models\HeroSlide::STATUS_PUBLISHED)
                                    <span class="badge-green">Published</span>
                                @else
                                    <span class="badge-dark">Unpublished</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.hero-sliders.preview', $heroSlide) }}" class="btn-ghost px-3 py-1.5 text-xs" aria-label="Preview {{ $heroSlide->movie_title_en }}">
                                        <x-icon name="eye" :size="14" /> Preview
                                    </a>
                                    <a href="{{ route('admin.hero-sliders.edit', $heroSlide) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                        <x-icon name="edit" :size="14" /> Edit
                                    </a>
                                    <form action="{{ route('admin.hero-sliders.status', $heroSlide) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-ghost px-3 py-1.5 text-xs">
                                            {{ $heroSlide->status === \App\Models\HeroSlide::STATUS_PUBLISHED ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.hero-sliders.destroy', $heroSlide) }}" method="POST" data-confirm="Delete this slide and its uploaded images? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost px-3 py-1.5 text-xs text-cg-red">
                                            <x-icon name="trash" :size="14" /> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-medium text-cg-white">No hero slides yet</p>
                                <p class="mt-1 text-sm text-cg-muted">Add a slide to manage the homepage hero without editing templates.</p>
                                <a href="{{ route('admin.hero-sliders.create') }}" class="btn-primary mt-4">Add New Slide</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
