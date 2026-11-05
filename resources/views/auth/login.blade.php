@extends('layouts.app')

@section('title', ui('login', 'Login'))

@section('content')
    <div class="relative flex min-h-[70vh] items-center justify-center px-4 py-14">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-cg-red/15 to-transparent"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-7 text-center">
                <a href="{{ route('home') }}" class="inline-flex justify-center"><x-logo :size="44" /></a>
                <h1 class="mt-5 font-display text-3xl tracking-wide text-cg-white">{{ ui('login_title', 'Welcome back') }}</h1>
                <p class="mt-1.5 text-sm text-cg-muted">{{ ui('login_sub', 'Log in to manage your bookings and tickets.') }}</p>
            </div>

            <form action="{{ route('login.store') }}" method="POST" class="card space-y-4 p-6 sm:p-7">
                @csrf

                <div>
                    <label class="label" for="login-email">{{ ui('email_address', 'Email address') }}</label>
                    <input
                        id="login-email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="input"
                        autocomplete="email"
                        required
                        maxlength="160"
                    >
                </div>

                <div>
                    <label class="label" for="login-password">{{ ui('password', 'Password') }}</label>
                    <div class="relative">
                        <input id="login-password" type="password" name="password" class="input pr-11" autocomplete="current-password" required>
                        <button type="button" data-password-toggle="login-password" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-cg-dim hover:text-cg-white" aria-label="{{ ui('show_password', 'Show password') }}">
                            <x-icon name="eye-off" :size="17" />
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-cg-muted">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-cg-border bg-cg-charcoal accent-cg-red">
                        {{ ui('remember_me', 'Remember me') }}
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-cg-red hover:underline">
                        {{ ui('forgot_password', 'Forgot your password?') }}
                    </a>
                </div>

                <button type="submit" class="btn-primary btn-lg btn-block">
                    {{ ui('login_now', 'Log in') }}
                </button>

                <p class="text-center text-sm text-cg-muted">
                    {{ ui('no_account', 'New to CineGo?') }}
                    <a href="{{ route('register') }}" class="font-semibold text-cg-red hover:underline">{{ ui('register_now', 'Register now') }}</a>
                </p>
            </form>
        </div>
    </div>
@endsection
