@extends('layouts.admin')

@section('title', $cinema->exists ? ui('edit_cinema', 'Edit cinema') : ui('add_cinema', 'Add cinema'))

@php($selectedCityId = (string) old('city_id', $cinema->city_id))

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.cinemas.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> {{ ui('back', 'Back') }}
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
        <form
            id="cinema-form"
            action="{{ $cinema->exists ? route('admin.cinemas.update', $cinema) : route('admin.cinemas.store') }}"
            method="POST"
            class="space-y-6"
        >
            @csrf
            @if ($cinema->exists) @method('PUT') @endif

            <section class="card space-y-4 p-5 sm:p-6">
                <h2 class="font-semibold text-cg-white">{{ ui('cinema_details', 'Cinema details') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="name">{{ ui('cinema_name', 'Cinema name') }} *</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $cinema->name) }}" class="input" required maxlength="120">
                    </div>
                    <div>
                        <label class="label" for="name_np">{{ ui('cinema_name_np', 'Name (नेपाली)') }}</label>
                        <input id="name_np" type="text" name="name_np" value="{{ old('name_np', $cinema->name_np) }}" class="input" maxlength="120">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="city_id">{{ ui('select_city', 'City') }} *</label>
                        <select id="city_id" name="city_id" class="input cursor-pointer" required>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected($selectedCityId === (string) $city->id)>
                                    {{ $city->display_name() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="slug">{{ ui('slug', 'Slug') }}</label>
                        <input id="slug" type="text" name="slug" value="{{ old('slug', $cinema->slug) }}" class="input" maxlength="140" placeholder="auto-generated">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="address">{{ ui('address', 'Address') }} *</label>
                        <input id="address" type="text" name="address" value="{{ old('address', $cinema->address) }}" class="input" required maxlength="200">
                    </div>
                    <div>
                        <label class="label" for="address_np">{{ ui('address_np', 'Address (नेपाली)') }}</label>
                        <input id="address_np" type="text" name="address_np" value="{{ old('address_np', $cinema->address_np) }}" class="input" maxlength="200">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="phone">{{ ui('phone_number', 'Phone') }}</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $cinema->phone) }}" class="input" maxlength="40">
                    </div>
                    <div>
                        <label class="label" for="email">{{ ui('email_address', 'Email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $cinema->email) }}" class="input" maxlength="160">
                    </div>
                </div>

                <div>
                    <label class="label" for="facilities">{{ ui('facilities', 'Facilities') }}</label>
                    <input id="facilities" type="text" name="facilities"
                        value="{{ old('facilities', is_array($cinema->facilities ?? null) ? implode(', ', $cinema->facilities) : null) }}"
                        class="input" maxlength="500" placeholder="Parking, Snacks, IMAX, 3D">
                    <p class="mt-1.5 text-[11px] text-cg-dim">{{ ui('comma_separated', 'Separate each facility with a comma.') }}</p>
                </div>

                <div>
                    <label class="label" for="image_url">{{ ui('banner', 'Image path') }}</label>
                    <input id="image_url" type="text" name="image_url" value="{{ old('image_url', $cinema->image_url) }}" class="input" maxlength="300" placeholder="/img/banners/banner-1.svg">
                </div>

                <div>
                    <label class="label" for="description">{{ ui('about', 'About') }}</label>
                    <textarea id="description" name="description" rows="4" class="input resize-y" maxlength="2000">{{ old('description', $cinema->description) }}</textarea>
                </div>

                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-cg-border bg-cg-charcoal px-3.5 py-3 transition hover:border-cg-red">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-cg-border bg-cg-charcoal accent-cg-red"
                        @checked(old('is_active', $cinema->is_active ?? true))>
                    <span class="text-sm text-cg-white">{{ ui('active', 'Active (visible on site)') }}</span>
                </label>
            </section>

            <button type="submit" class="btn-primary btn-lg">
                <x-icon name="check" :size="17" />
                {{ $cinema->exists ? ui('save', 'Save changes') : ui('create', 'Create cinema') }}
            </button>
        </form>

        <aside class="space-y-6">
            @if ($cinema->exists)
                {{-- Screens (halls) — created, edited and deleted inline --}}
                <section class="card p-5">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <h2 class="font-semibold text-cg-white">{{ ui('screens', 'Screens') }}</h2>
                        <span class="badge-dark">{{ $cinema->halls->count() }}</span>
                    </div>

                    <div class="space-y-4">
                        @forelse ($cinema->halls as $hall)
                            <form action="{{ route('admin.halls.update', $hall) }}" method="POST"
                                class="rounded-xl border border-cg-border bg-cg-charcoal p-3.5" data-confirm="{{ ui('save_screen_confirm', 'Save this screen? Seats are regenerated to match.') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="cinema_id" value="{{ $cinema->id }}">

                                <div class="mb-2.5 flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-cg-white">{{ $hall->name }}</span>
                                    <span class="text-[11px] text-cg-dim">{{ $hall->seats()->count() }} {{ ui('seats', 'seats') }}</span>
                                </div>

                                <div class="mb-2 grid grid-cols-3 gap-2">
                                    <input type="text" name="name" value="{{ old('name', $hall->name) }}" class="input h-9 min-h-9 px-2.5 text-xs" required maxlength="60" aria-label="{{ ui('name', 'Name') }}">
                                    <input type="number" name="total_rows" value="{{ old('total_rows', $hall->total_rows) }}" class="input h-9 min-h-9 px-2.5 text-xs" min="3" max="26" aria-label="{{ ui('rows', 'Rows') }}" title="{{ ui('rows', 'Rows') }}">
                                    <input type="number" name="seats_per_row" value="{{ old('seats_per_row', $hall->seats_per_row) }}" class="input h-9 min-h-9 px-2.5 text-xs" min="4" max="30" aria-label="{{ ui('seats_per_row', 'Seats/row') }}" title="{{ ui('seats_per_row', 'Seats/row') }}">
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <label class="flex items-center gap-2 text-xs text-cg-muted">
                                        <input type="checkbox" name="is_active" value="1" class="h-3.5 w-3.5 accent-cg-red" @checked(old("is_active.$hall->id", $hall->is_active))>
                                        {{ ui('active', 'Active') }}
                                    </label>
                                    <div class="flex gap-1.5">
                                        <button type="submit" class="btn-outline px-2.5 py-1 text-[11px]">
                                            <x-icon name="check" :size="12" /> {{ ui('save', 'Save') }}
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <form action="{{ route('admin.halls.destroy', $hall) }}" method="POST" class="-mt-2 text-right"
                                data-confirm="{{ ui('delete_confirm_screen', 'Delete this screen and its seats? This cannot be undone.') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] font-medium text-cg-red hover:underline">
                                    <x-icon name="trash" :size="11" class="inline" /> {{ ui('delete', 'Delete') }}
                                </button>
                            </form>
                        @empty
                            <p class="text-sm text-cg-muted">{{ ui('no_screens', 'No screens yet.') }}</p>
                        @endforelse
                    </div>

                    {{-- Add screen --}}
                    <form action="{{ route('admin.halls.store') }}" method="POST" class="mt-5 border-t border-cg-border-soft pt-4">
                        @csrf
                        <input type="hidden" name="cinema_id" value="{{ $cinema->id }}">

                        <p class="mb-2.5 text-xs font-semibold uppercase tracking-wide text-cg-dim">{{ ui('add_screen', 'Add screen') }}</p>

                        <div class="space-y-2">
                            <input type="text" name="name" class="input h-10 min-h-10 text-sm" required maxlength="60"
                                placeholder="{{ ui('screen_name', 'Screen name') }} (e.g. Hall 1)">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="total_rows" class="input h-10 min-h-10 text-sm" required min="3" max="26" value="8"
                                    placeholder="{{ ui('rows', 'Rows') }}" aria-label="{{ ui('rows', 'Rows') }}">
                                <input type="number" name="seats_per_row" class="input h-10 min-h-10 text-sm" required min="4" max="30" value="14"
                                    placeholder="{{ ui('seats_per_row', 'Seats/row') }}" aria-label="{{ ui('seats_per_row', 'Seats/row') }}">
                            </div>
                            <p class="text-[11px] text-cg-dim">{{ ui('seat_gen_note', 'Seats are generated automatically; the last row becomes recliner, the one before premium.') }}</p>
                        </div>

                        <button type="submit" class="btn-primary mt-3 w-full justify-center py-2.5 text-sm">
                            <x-icon name="plus" :size="15" /> {{ ui('add_screen', 'Add screen') }}
                        </button>
                    </form>
                </section>
            @else
                <section class="card p-5">
                    <p class="text-sm text-cg-muted">{{ ui('cinema_first_note', 'Save the cinema first, then add its screens.') }}</p>
                </section>
            @endif
        </aside>
    </div>
@endsection
