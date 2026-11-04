@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="container-page py-10">
        <x-breadcrumbs :items="[['label' => $title]]" />

        <div class="max-w-3xl">
            <p class="section-kicker">CineGo</p>
            <h1 class="section-title">{{ $title }}</h1>
            <p class="mt-3 text-cg-muted">{{ $intro }}</p>
            <p class="mt-2 text-xs text-cg-dim">{{ ui('last_updated', 'Last updated') }}: {{ now()->format('F j, Y') }}</p>
        </div>

        <div class="mt-10 max-w-3xl space-y-5">
            @foreach ($sections as $index => $section)
                <section class="card p-5 sm:p-6">
                    <div class="mb-2 flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-cg-red/15 text-sm font-bold text-cg-red">
                            {{ $index + 1 }}
                        </span>
                        <h2 class="text-lg font-semibold text-cg-white">{{ $section['heading'] }}</h2>
                    </div>
                    <p class="pl-11 text-sm leading-relaxed text-cg-muted">{{ $section['body'] }}</p>
                </section>
            @endforeach
        </div>

        <div class="mt-10 max-w-3xl">
            <a href="{{ route('pages.contact') }}" class="btn-outline">
                <x-icon name="mail" :size="16" /> {{ ui('contact_title', 'Contact us') }}
            </a>
        </div>
    </div>
@endsection
