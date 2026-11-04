@extends('layouts.app')

@section('title', ui('offers', 'Offers'))

@section('content')
    <div class="container-page pt-10">
        <x-breadcrumbs :items="[['label' => ui('offers', 'Offers')]]" />

        <div class="mb-8">
            <p class="section-kicker">{{ ui('special_offers', 'Special Offers') }}</p>
            <h1 class="section-title">{{ ui('special_offers', 'Special Offers') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-cg-muted sm:text-base">
                {{ ui('offers_sub', 'Apply a promo code at checkout to instantly reduce your ticket total.') }}
            </p>
        </div>

        @if ($offers->isEmpty())
            <x-empty
                :title="ui('no_offers', 'No offers running right now.')"
                :text="ui('check_back_soon', 'Check back soon for new deals.')"
                icon="tag"
                :action-label="ui('movies', 'Movies')"
                :action-href="route('movies.index')"
            />
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($offers as $offer)
                    <article class="card card-hover relative flex flex-col overflow-hidden p-6">
                        <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-cg-red/15 blur-2xl"></div>
                        <div class="absolute -bottom-10 -left-10 h-24 w-24 rounded-full bg-cg-red/10 blur-2xl"></div>

                        <div class="relative flex items-start justify-between gap-3">
                            <span class="badge-red text-sm">{{ $offer->badge_label() }}</span>
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-cg-red/15 text-cg-red">
                                <x-icon name="tag" :size="21" />
                            </span>
                        </div>

                        <h2 class="relative mt-4 text-xl font-semibold text-cg-white">{{ $offer->localised_title() }}</h2>
                        <p class="relative mt-2 flex-1 text-sm leading-relaxed text-cg-muted">{{ $offer->localised_description() }}</p>

                        @if ($offer->code)
                            <button
                                type="button"
                                data-copy="{{ $offer->code }}"
                                class="relative mt-5 flex items-center justify-between gap-3 rounded-lg border border-dashed border-cg-red/60 bg-cg-red/10 px-4 py-3 font-mono text-sm font-bold tracking-widest text-cg-red transition hover:bg-cg-red/20"
                            >
                                <span>{{ $offer->code }}</span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold normal-case tracking-normal">
                                    <x-icon name="copy" :size="14" /> {{ ui('copy', 'Copy') }}
                                </span>
                            </button>
                        @endif

                        <div class="relative mt-4 flex items-center justify-between text-xs text-cg-dim">
                            <span>{{ $offer->valid_label() }}</span>
                            @if ($offer->min_amount)
                                <span>{{ ui('min_spend', 'Min. spend') }} Rs. {{ number_format($offer->min_amount) }}</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
