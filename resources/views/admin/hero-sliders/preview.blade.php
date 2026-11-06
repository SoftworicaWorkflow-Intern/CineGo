@extends('layouts.admin')

@section('title', 'Preview Hero Slide')

@section('content')
    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('admin.hero-sliders.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> Back to Hero Slider
        </a>
        <a href="{{ route('admin.hero-sliders.edit', $heroSlide) }}" class="btn-outline px-4 py-2 text-sm">
            <x-icon name="edit" :size="15" /> Edit slide
        </a>
    </div>

    <section class="relative mx-auto flex min-h-[min(650px,75svh)] max-w-6xl items-end overflow-hidden rounded-2xl bg-cg-charcoal p-8 sm:p-12" aria-label="Hero slide preview">
        @if ($heroSlide->desktop_image)
            <img src="{{ $heroSlide->desktop_image_url() }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-black/10"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-transparent"></div>
        <div class="relative z-10 max-w-2xl text-white">
            @if ($heroSlide->localized_label())
                <p class="mb-4 text-xs font-semibold uppercase tracking-[0.18em] text-white/80">{{ $heroSlide->localized_label() }}</p>
            @endif
            <p class="mb-2 text-sm text-white/70">{{ $heroSlide->localized_movie_title() }}</p>
            <h2 class="font-display text-4xl leading-tight sm:text-6xl">{{ $heroSlide->localized_title() }}</h2>
            <p class="mt-4 max-w-xl text-sm leading-7 text-white/80">{{ $heroSlide->localized_description() }}</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ $heroSlide->primary_button_url }}" class="btn-primary">{{ $heroSlide->localized_primary_button_text() }}</a>
                @if ($heroSlide->secondary_button_text_en && $heroSlide->secondary_button_url)
                    <a href="{{ $heroSlide->secondary_button_url }}" class="btn-outline">{{ $heroSlide->localized_secondary_button_text() }}</a>
                @endif
            </div>
            <p class="mt-5 text-xs text-white/70">
                {{ $heroSlide->language }}
                @if ($heroSlide->duration) · {{ $heroSlide->duration }} min @endif
                @if ($heroSlide->release_date) · {{ $heroSlide->release_date->format('M j, Y') }} @endif
            </p>
        </div>
    </section>

    @if ($heroSlide->mobile_image)
        <section class="mt-8">
            <h2 class="mb-3 text-sm font-semibold text-cg-white">Mobile image preview</h2>
            <div class="relative mx-auto aspect-[4/5] w-64 overflow-hidden rounded-2xl border border-cg-border bg-cg-charcoal">
                <img src="{{ $heroSlide->mobile_image_url() }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-4 text-sm font-semibold text-white">{{ $heroSlide->localized_title() }}</div>
            </div>
        </section>
    @endif
@endsection
