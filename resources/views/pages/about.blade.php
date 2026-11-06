@extends('layouts.app')

@section('title', ui('about_title', 'About CineGo'))

@section('content')
    <section class="bg-[#f5f2eb] pb-14 pt-6 sm:pb-20 sm:pt-8">
        <div class="container-page">
            <x-breadcrumbs :items="[['label' => ui('about', 'About Us')]]" />

            <figure class="relative aspect-[1.35/1] overflow-hidden rounded-[24px] bg-gray-200 shadow-[0_24px_70px_rgba(27,24,20,0.12)] sm:aspect-[2.35/1]">
                <img
                    src="{{ asset('img/about/cinema-screen.png') }}"
                    alt="{{ ui('about_hero_alt', 'Rows of red cinema seats facing the big screen') }}"
                    class="h-full w-full object-cover"
                    fetchpriority="high"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-black/5" aria-hidden="true"></div>
                <figcaption class="absolute bottom-5 left-5 rounded-full border border-white/30 bg-black/30 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white backdrop-blur-sm sm:bottom-7 sm:left-7">
                    {{ ui('about_image_caption', 'Made for movie moments') }}
                </figcaption>
            </figure>

            <div class="grid gap-6 py-12 sm:gap-10 sm:py-16 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-cg-red">{{ ui('about', 'About Us') }}</p>
                    <h1 class="max-w-2xl font-serif text-4xl font-semibold leading-tight tracking-tight text-gray-950 sm:text-5xl lg:text-6xl">
                        {{ ui('about_sub', 'Discover Movies. Book Your Seats.') }}
                    </h1>
                </div>
                <div class="space-y-4 text-[15px] leading-7 text-gray-600 sm:text-base">
                    <p class="text-lg leading-8 text-gray-900 sm:text-xl">
                        {{ ui('about_p1', 'CineGo is a modern movie ticket booking platform built for Nepali cinema lovers. We make it simple to discover what is playing, find the nearest hall and lock in your favourite seats — all from your phone.') }}
                    </p>
                    <p>
                        {{ ui('about_p2', 'Every branch, showtime and seat you see on CineGo comes straight from our cinema partners in real time. There are no printed queues and no guesswork: choose a movie, choose a showtime, choose your seats, pay with eSewa, Khalti or card, and your QR ticket is ready instantly.') }}
                    </p>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <a href="{{ route('movies.index') }}" class="btn-primary btn-lg">
                            <x-icon name="film" :size="18" /> {{ ui('explore_movies', 'Explore Movies') }}
                        </a>
                        <a href="{{ route('pages.contact') }}" class="btn-lg rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-800 transition hover:border-cg-red hover:text-cg-red">
                            <x-icon name="mail" :size="17" /> {{ ui('contact', 'Contact') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-20">
        <div class="container-page">
            <div class="max-w-3xl">
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-cg-red">{{ ui('our_values', 'Our values') }}</p>
                <h2 class="font-serif text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl">
                    {{ ui('what_we_stand_for', 'What we stand for') }}
                </h2>
                <p class="mt-4 text-base leading-7 text-gray-600 sm:text-lg">
                    {{ ui('about_values_intro', 'A better movie night starts before the opening credits. We make every step clear, convenient and welcoming.') }}
                </p>
            </div>

            <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['icon' => 'zap', 'title' => ui('value1_t', 'Simplicity first'), 'text' => ui('value1_b', 'If a customer needs help to book, we have failed.')],
                    ['icon' => 'shield-check', 'title' => ui('value2_t', 'Fair and transparent'), 'text' => ui('value2_b', 'Server-side pricing, clear refund rules, no hidden fees.')],
                    ['icon' => 'globe', 'title' => ui('value3_t', 'Made for Nepal'), 'text' => ui('value3_b', 'Local payment methods and full नेपाली language support.')],
                ] as $value)
                    <article class="rounded-2xl border border-gray-200 bg-[#fbfaf7] p-6 sm:p-7">
                        <span class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-cg-red/10 text-cg-red">
                            <x-icon :name="$value['icon']" :size="21" />
                        </span>
                        <h3 class="font-serif text-2xl font-semibold text-gray-950">{{ $value['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">{{ $value['text'] }}</p>
                    </article>
                @endforeach
            </div>

        </div>
    </section>

    <section class="bg-[#f5f2eb] py-14 sm:py-20">
        <div class="container-page grid items-center gap-8 lg:grid-cols-2 lg:gap-14">
            <div class="space-y-5">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cg-red">{{ ui('our_story', 'Our story') }}</p>
                <h2 class="font-serif text-4xl font-semibold leading-tight tracking-tight text-gray-950 sm:text-5xl">
                    {{ ui('about_story_heading', 'A simpler way to make movie memories.') }}
                </h2>
                <p class="text-[15px] leading-7 text-gray-600 sm:text-base">
                    {{ ui('about_p3', 'We started CineGo because booking a movie ticket in Nepal still meant standing in line — or juggling phone calls with the box office. Our team of developers and cinema fans set out to build the simplest possible booking experience: one clean website, one short flow, one digital ticket.') }}
                </p>
                <p class="text-[15px] leading-7 text-gray-600 sm:text-base">
                    {{ ui('about_p4', 'Today CineGo connects audiences with Kawasoti in English and Nepali, on any device. We are just getting started.') }}
                </p>
            </div>

            <figure class="relative aspect-[1.25/1] overflow-hidden rounded-[24px] bg-gray-200 shadow-[0_24px_70px_rgba(27,24,20,0.12)] sm:aspect-[1.45/1]">
                <img
                    src="{{ asset('img/about/cinema-hall.png') }}"
                    alt="{{ ui('about_story_alt', 'A wide view of a cinema hall filled with red seats') }}"
                    class="h-full w-full object-cover"
                    loading="lazy"
                >
                <figcaption class="absolute bottom-5 left-5 rounded-full bg-white/90 px-4 py-2 text-xs font-semibold text-gray-900 shadow-sm">
                    {{ ui('about_story_caption', 'Your next favourite film is waiting') }}
                </figcaption>
            </figure>
        </div>
    </section>

    <section class="bg-white py-12 sm:py-16">
        <div class="container-page flex flex-col gap-8 rounded-3xl border border-gray-200 bg-[#fbfaf7] p-6 sm:p-9 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cg-red">{{ ui('contact_details', 'Contact details') }}</p>
                <h2 class="mt-2 font-serif text-3xl font-semibold text-gray-950">{{ ui('about_contact_heading', 'We would love to hear from you.') }}</h2>
                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-3 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-2"><x-icon name="map-pin" :size="16" class="text-cg-red" /> Kawasoti-3, Nepal</span>
                    <a href="tel:+9779800000000" class="inline-flex items-center gap-2 transition hover:text-cg-red"><x-icon name="phone" :size="16" class="text-cg-red" /> +977-9800-000-000</a>
                    <a href="mailto:support@cinego.com.np" class="inline-flex items-center gap-2 transition hover:text-cg-red"><x-icon name="mail" :size="16" class="text-cg-red" /> support@cinego.com.np</a>
                </div>
            </div>
            <a href="{{ route('pages.contact') }}" class="btn-primary btn-lg shrink-0">
                {{ ui('contact', 'Contact') }} <x-icon name="arrow-right" :size="17" />
            </a>
        </div>
    </section>
@endsection
