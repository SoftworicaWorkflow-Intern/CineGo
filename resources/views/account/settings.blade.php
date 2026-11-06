@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', ui('account_settings', 'Account settings'))

@section('content')
    <div class="container-page py-10">
        <x-breadcrumbs :items="[['label' => ui('my_account', 'My Account'), 'href' => route('account.dashboard')], ['label' => ui('account_settings', 'Account settings')]]" />

        <div class="mb-8">
            <p class="section-kicker">{{ ui('my_account', 'My Account') }}</p>
            <h1 class="section-title">{{ ui('account_settings', 'Account settings') }}</h1>
        </div>

        @include('partials.account-nav')

        <div class="grid max-w-4xl gap-6 lg:grid-cols-2">
            {{-- Change password --}}
            <form id="security" action="{{ route('account.password.update') }}" method="POST" class="card space-y-4 p-6">
                @csrf

                <h2 class="flex items-center gap-2 font-semibold text-cg-white">
                    <x-icon name="shield-check" :size="17" class="text-cg-red" />
                    {{ ui('change_password', 'Change password') }}
                </h2>

                <div>
                    <label class="label" for="current-password">{{ ui('current_password', 'Current password') }}</label>
                    <div class="relative">
                        <input id="current-password" type="password" name="current_password" class="input pr-11" required autocomplete="current-password">
                        <button type="button" data-password-toggle="current-password" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-cg-dim hover:text-cg-white" aria-label="{{ ui('show_password', 'Show password') }}"><x-icon name="eye-off" :size="17" /></button>
                    </div>
                </div>

                <div>
                    <label class="label" for="new-password">{{ ui('new_password', 'New password') }}</label>
                    <div class="relative">
                        <input id="new-password" type="password" name="password" class="input pr-11" required autocomplete="new-password">
                        <button type="button" data-password-toggle="new-password" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-cg-dim hover:text-cg-white" aria-label="{{ ui('show_password', 'Show password') }}"><x-icon name="eye-off" :size="17" /></button>
                    </div>
                    <p class="mt-1.5 text-xs text-cg-dim">{{ ui('password_hint', 'At least 8 characters.') }}</p>
                </div>

                <div>
                    <label class="label" for="confirm-password">{{ ui('confirm_password', 'Confirm password') }}</label>
                    <div class="relative">
                        <input id="confirm-password" type="password" name="password_confirmation" class="input pr-11" required autocomplete="new-password">
                        <button type="button" data-password-toggle="confirm-password" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-cg-dim hover:text-cg-white" aria-label="{{ ui('show_password', 'Show password') }}"><x-icon name="eye-off" :size="17" /></button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">{{ ui('change_password', 'Change password') }}</button>
            </form>

            @unless ($user->is_admin)
                <form id="preferences" action="{{ route('account.preferences.update') }}" method="POST" class="card space-y-4 p-6">
                    @csrf

                    <h2 class="flex items-center gap-2 font-semibold text-cg-white">
                        <x-icon name="globe" :size="17" class="text-cg-red" />
                        {{ ui('language_preference', 'Language preference') }}
                    </h2>

                    <div class="grid grid-cols-2 gap-3">
                        @foreach (['en' => 'English', 'np' => 'नेपाली'] as $code => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="preferred_language" value="{{ $code }}" class="peer sr-only"
                                    @checked(old('preferred_language', $user->preferred_language ?? 'en') === $code)>
                                <span class="flex items-center justify-center rounded-xl border border-cg-border bg-cg-charcoal px-4 py-3.5 font-semibold text-cg-white transition hover:border-cg-red peer-checked:border-cg-red peer-checked:bg-cg-red/10 peer-checked:text-cg-red">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div>
                        <label class="label" for="settings-city">{{ ui('select_city', 'Preferred city') }}</label>
                        <select id="settings-city" name="city_id" class="input cursor-pointer">
                            <option value="">{{ ui('all_cities', 'All Cities') }}</option>
                            @foreach (\App\Models\City::active()->orderBy('name')->get() as $city)
                                <option value="{{ $city->id }}" @selected((string) old('city_id', $user->city_id) === (string) $city->id)>
                                    {{ $city->display_name() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="flex items-start gap-2 text-xs text-cg-dim">
                        <x-icon name="info" :size="14" class="mt-0.5 shrink-0 text-cg-red" />
                        {{ ui('language_note', 'Your choice is saved to your account and applied on your next visit.') }}
                    </p>

                    <button type="submit" class="btn-primary">{{ ui('save', 'Save changes') }}</button>
                </form>
            @endunless
        </div>
    </div>
@endsection
