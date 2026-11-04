@extends('layouts.app')

@section('title', ui('register', 'Register'))

@section('content')
    <div class="relative flex min-h-[70vh] items-center justify-center px-4 py-14">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-cg-red/15 to-transparent"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-7 text-center">
                <a href="{{ route('home') }}" class="inline-flex justify-center"><x-logo :size="44" /></a>
                <h1 class="mt-5 font-display text-3xl tracking-wide text-cg-white">{{ ui('register_title', 'Create your account') }}</h1>
                <p class="mt-1.5 text-sm text-cg-muted">{{ ui('register_sub', 'Book faster and keep every ticket in one place.') }}</p>
            </div>

            <form action="{{ route('register.store') }}" method="POST" class="card space-y-4 p-6 sm:p-7">
                @csrf

                <div>
                    <label class="label" for="register-name">{{ ui('name', 'Name') }}</label>
                    <input id="register-name" type="text" name="name" value="{{ old('name') }}" class="input" autocomplete="name" required maxlength="100">
                </div>

                <div>
                    <label class="label" for="register-email">{{ ui('email_address', 'Email address') }}</label>
                    <input id="register-email" type="email" name="email" value="{{ old('email') }}" class="input" autocomplete="email" required maxlength="160">
                </div>

                <div>
                    <label class="label" for="register-phone">{{ ui('mobile_number', 'Mobile number') }}</label>
                    <input id="register-phone" type="tel" name="phone" value="{{ old('phone') }}" class="input" autocomplete="tel" maxlength="20" placeholder="98XXXXXXXX">
                </div>

                <div>
                    <label class="label" for="register-city">{{ ui('select_city', 'Select City') }}</label>
                    <select id="register-city" name="city_id" class="input cursor-pointer">
                        <option value="">{{ ui('all_cities', 'All Cities') }}</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected((string) old('city_id') === (string) $city->id)>
                                {{ $city->display_name() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label" for="register-password">{{ ui('new_password', 'Password') }}</label>
                    <input id="register-password" type="password" name="password" class="input" autocomplete="new-password" required>
                    <p class="mt-1.5 text-xs text-cg-dim">{{ ui('password_hint', 'At least 8 characters.') }}</p>
                </div>

                <div>
                    <label class="label" for="register-password-confirmation">{{ ui('confirm_password', 'Confirm password') }}</label>
                    <input id="register-password-confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn-primary btn-lg btn-block">{{ ui('register_now', 'Register now') }}</button>

                <p class="text-center text-sm text-cg-muted">
                    {{ ui('have_account_q', 'Already have an account?') }}
                    <a href="{{ route('login') }}" class="font-semibold text-cg-red hover:underline">{{ ui('login_now', 'Log in') }}</a>
                </p>
            </form>
        </div>
    </div>
@endsection
