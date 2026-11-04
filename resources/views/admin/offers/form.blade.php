@extends('layouts.admin')

@section('title', $offer->exists ? ui('edit_offer', 'Edit offer') : ui('add_offer', 'Add offer'))

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.offers.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> {{ ui('back', 'Back') }}
        </a>
    </div>

    <form
        action="{{ $offer->exists ? route('admin.offers.update', $offer) : route('admin.offers.store') }}"
        method="POST"
        class="max-w-3xl space-y-6"
    >
        @csrf
        @if ($offer->exists) @method('PUT') @endif

        <section class="card space-y-4 p-5 sm:p-6">
            <h2 class="font-semibold text-cg-white">{{ ui('offer_details', 'Offer details') }}</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="title">{{ ui('title', 'Title') }} *</label>
                    <input id="title" type="text" name="title" value="{{ old('title', $offer->title) }}" class="input" required maxlength="120">
                </div>
                <div>
                    <label class="label" for="title_np">{{ ui('title_np', 'Title (नेपाली)') }}</label>
                    <input id="title_np" type="text" name="title_np" value="{{ old('title_np', $offer->title_np) }}" class="input" maxlength="120">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="description">{{ ui('description', 'Description') }} *</label>
                    <textarea id="description" name="description" rows="3" class="input resize-y" required maxlength="1000">{{ old('description', $offer->description) }}</textarea>
                </div>
                <div>
                    <label class="label" for="description_np">{{ ui('description_np', 'Description (नेपाली)') }}</label>
                    <textarea id="description_np" name="description_np" rows="3" class="input resize-y" maxlength="1000">{{ old('description_np', $offer->description_np) }}</textarea>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="label" for="code">{{ ui('promo_code', 'Promo code') }}</label>
                    <input id="code" type="text" name="code" value="{{ old('code', $offer->code) }}" class="input uppercase" maxlength="40" placeholder="CINEGO10" autocomplete="off">
                    <p class="mt-1.5 text-[11px] text-cg-dim">{{ ui('code_optional', 'Optional — leave blank for display-only offers.') }}</p>
                </div>
                <div>
                    <label class="label" for="badge">{{ ui('badge', 'Badge') }}</label>
                    <input id="badge" type="text" name="badge" value="{{ old('badge', $offer->badge) }}" class="input" maxlength="30" placeholder="{{ ui('hot', 'HOT') }}">
                </div>
                <div>
                    <label class="label" for="image_url">{{ ui('image', 'Image path') }}</label>
                    <input id="image_url" type="text" name="image_url" value="{{ old('image_url', $offer->image_url) }}" class="input" maxlength="300" placeholder="/img/banners/banner-2.svg">
                </div>
            </div>
        </section>

        <section class="card space-y-4 p-5 sm:p-6">
            <h2 class="font-semibold text-cg-white">{{ ui('discount_settings', 'Discount settings') }}</h2>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="label" for="discount_type">{{ ui('discount_type', 'Type') }} *</label>
                    <select id="discount_type" name="discount_type" class="input cursor-pointer" required>
                        <option value="percentage" @selected(old('discount_type', $offer->discount_type ?? 'percentage') === 'percentage')>
                            {{ ui('percentage', 'Percentage (%)') }}
                        </option>
                        <option value="fixed" @selected(old('discount_type', $offer->discount_type) === 'fixed')>
                            {{ ui('fixed_amount', 'Fixed (Rs.)') }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="label" for="discount_value">{{ ui('discount_value', 'Value') }} *</label>
                    <input id="discount_value" type="number" name="discount_value" value="{{ old('discount_value', $offer->discount_value ?? 10) }}" class="input" required min="0" step="0.01">
                </div>
                <div>
                    <label class="label" for="min_amount">{{ ui('min_spend', 'Minimum spend') }}</label>
                    <input id="min_amount" type="number" name="min_amount" value="{{ old('min_amount', $offer->min_amount) }}" class="input" min="0" step="1" placeholder="0">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="valid_from">{{ ui('valid_from', 'Valid from') }}</label>
                    <input id="valid_from" type="date" name="valid_from" value="{{ old('valid_from', $offer->valid_from?->format('Y-m-d')) }}" class="input">
                </div>
                <div>
                    <label class="label" for="valid_to">{{ ui('valid_to', 'Valid until') }}</label>
                    <input id="valid_to" type="date" name="valid_to" value="{{ old('valid_to', $offer->valid_to?->format('Y-m-d')) }}" class="input">
                </div>
            </div>

            <label class="flex w-fit cursor-pointer items-center gap-3 rounded-xl border border-cg-border bg-cg-charcoal px-4 py-3 transition hover:border-cg-red">
                <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded accent-cg-red"
                    @checked(old('is_active', $offer->is_active ?? true))>
                <span class="text-sm font-medium text-cg-white">{{ ui('active', 'Active (valid for checkout)') }}</span>
            </label>
        </section>

        <button type="submit" class="btn-primary btn-lg">
            <x-icon name="check" :size="17" />
            {{ $offer->exists ? ui('save', 'Save changes') : ui('create', 'Create offer') }}
        </button>
    </form>
@endsection
