@extends('layouts.app')

@section('title', ui('profile_settings', 'Profile settings'))

@section('content')
    <div class="container-page py-10">
        <x-breadcrumbs :items="[['label' => ui('my_account', 'My Account'), 'href' => route('account.dashboard')], ['label' => ui('profile_settings', 'Profile settings')]]" />

        <div class="mb-8">
            <p class="section-kicker">{{ ui('my_account', 'My Account') }}</p>
            <h1 class="section-title">{{ ui('profile_settings', 'Profile settings') }}</h1>
        </div>

        @include('partials.account-nav')

        <div class="max-w-2xl">
            <form action="{{ route('account.profile.update') }}" method="POST" class="card space-y-4 p-6">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="label" for="profile-name">{{ ui('full_name', 'Full name') }}</label>
                        <input id="profile-name" type="text" name="name" value="{{ old('name', $user->name) }}" class="input" required maxlength="100" autocomplete="name">
                    </div>

                    <div>
                        <label class="label" for="profile-email">{{ ui('email_address', 'Email address') }}</label>
                        <input id="profile-email" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required maxlength="160" autocomplete="email">
                    </div>

                    <div>
                        <label class="label" for="profile-phone">{{ ui('mobile_number', 'Mobile number') }}</label>
                        <input id="profile-phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="input" maxlength="20" autocomplete="tel" placeholder="98XXXXXXXX">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label" for="profile-city">{{ ui('select_city', 'Preferred city') }}</label>
                        <select id="profile-city" name="city_id" class="input cursor-pointer">
                            <option value="">{{ ui('all_cities', 'All Cities') }}</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected((string) old('city_id', $user->city_id) === (string) $city->id)>
                                    {{ $city->display_name() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-lg border border-cg-border bg-cg-charcoal px-4 py-3 text-xs text-cg-muted">
                    <x-icon name="info" :size="15" class="shrink-0 text-cg-red" />
                    {{ ui('member_since', 'Member since') }} {{ $user->created_at->format('F j, Y') }}
                </div>

                <button type="submit" class="btn-primary">{{ ui('save', 'Save changes') }}</button>
            </form>
        </div>
    </div>
@endsection
