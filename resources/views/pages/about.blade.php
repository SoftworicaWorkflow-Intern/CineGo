@extends('layouts.app')

@section('title', ui('about_title', 'About CineGo'))

@section('content')
    <div class="relative overflow-hidden border-b border-cg-border-soft">
        <div class="absolute inset-0 bg-gradient-to-br from-cg-red/25 via-transparent to-transparent"></div>
        <div class="container-page relative py-14">
            <x-breadcrumbs :items="[['label' => ui('about', 'About Us')]]" />
            <p class="section-kicker">{{ ui('about', 'About Us') }}</p>
            <h1 class="section-title max-w-3xl text-4xl sm:text-5xl">{{ ui('about_sub', 'Discover Movies. Book Your Seats.') }}</h1>
        </div>
    </div>

    <div class="container-page grid gap-10 py-14 lg:grid-cols-[1fr_340px]">
        <div class="space-y-8 text-[15px] leading-relaxed text-cg-muted">
            <p class="text-lg text-cg-white">
                {{ ui('about_p1', 'CineGo is a modern movie ticket booking platform built for Nepali cinema lovers. We make it simple to discover what is playing, find the nearest hall and lock in your favourite seats — all from your phone.') }}
            </p>

            <p>
                {{ ui('about_p2', 'Every branch, showtime and seat you see on CineGo comes straight from our cinema partners in real time. There are no printed queues and no guesswork: choose a movie, choose a showtime, choose your seats, pay with eSewa, Khalti or card, and your QR ticket is ready instantly.') }}
            </p>

            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['icon' => 'armchair', 'value' => '10+', 'label' => ui('about_stat1', 'Cinema halls')],
                    ['icon' => 'ticket', 'value' => '50K+', 'label' => ui('about_stat2', 'Tickets booked')],
                    ['icon' => 'map-pin', 'value' => '6', 'label' => ui('about_stat3', 'Cities across Nepal')],
                ] as $stat)
                    <div class="card p-5 text-center">
                        <span class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-cg-red/15 text-cg-red">
                            <x-icon :name="$stat['icon']" :size="21" />
                        </span>
                        <p class="font-display text-3xl text-cg-white">{{ $stat['value'] }}</p>
                        <p class="text-sm text-cg-muted">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>

            <h2 class="pt-2 text-2xl font-semibold text-cg-white">{{ ui('our_story', 'Our story') }}</h2>
            <p>
                {{ ui('about_p3', 'We started CineGo because booking a movie ticket in Nepal still meant standing in line — or juggling phone calls with the box office. Our team of developers and cinema fans set out to build the simplest possible booking experience: one clean website, one short flow, one digital ticket.') }}
            </p>
            <p>
                {{ ui('about_p4', 'Today CineGo connects audiences with Kawasoti in English and Nepali, on any device. We are just getting started.') }}
            </p>

            <div class="flex flex-wrap gap-3 pt-2">
                <a href="{{ route('movies.index') }}" class="btn-primary btn-lg">
                    <x-icon name="film" :size="18" /> {{ ui('explore_movies', 'Explore Movies') }}
                </a>
                <a href="{{ route('pages.contact') }}" class="btn-outline btn-lg">
                    <x-icon name="mail" :size="17" /> {{ ui('contact', 'Contact') }}
                </a>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="card p-5">
                <h2 class="mb-4 font-semibold text-cg-white">{{ ui('our_values', 'Our values') }}</h2>
                <ul class="space-y-4 text-sm">
                    @foreach ([
                        ['icon' => 'zap', 'title' => ui('value1_t', 'Simplicity first'), 'text' => ui('value1_b', 'If a customer needs help to book, we have failed.')],
                        ['icon' => 'shield-check', 'title' => ui('value2_t', 'Fair and transparent'), 'text' => ui('value2_b', 'Server-side pricing, clear refund rules, no hidden fees.')],
                        ['icon' => 'globe', 'title' => ui('value3_t', 'Made for Nepal'), 'text' => ui('value3_b', 'Local payment methods and full नेपाली language support.')],
                    ] as $value)
                        <li class="flex gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cg-red/15 text-cg-red">
                                <x-icon :name="$value['icon']" :size="17" />
                            </span>
                            <span>
                                <span class="block font-medium text-cg-white">{{ $value['title'] }}</span>
                                <span class="text-cg-muted">{{ $value['text'] }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card p-5">
                <h2 class="mb-3 font-semibold text-cg-white">{{ ui('contact_details', 'Contact details') }}</h2>
                <p class="flex items-start gap-2 text-sm text-cg-muted">
                    <x-icon name="map-pin" :size="15" class="mt-0.5 shrink-0 text-cg-red" />
                    Kawasoti-3, Nepal
                </p>
                <p class="mt-2.5 flex items-center gap-2 text-sm text-cg-muted">
                    <x-icon name="phone" :size="15" class="shrink-0 text-cg-red" />
                    <a href="tel:+9779800000000" class="hover:text-cg-white">+977-9800-000-000</a>
                </p>
                <p class="mt-2.5 flex items-center gap-2 text-sm text-cg-muted">
                    <x-icon name="mail" :size="15" class="shrink-0 text-cg-red" />
                    <a href="mailto:support@cinego.com.np" class="hover:text-cg-white">support@cinego.com.np</a>
                </p>
            </div>
        </aside>
    </div>
@endsection
