@extends('layouts.app')

@section('title', ui('reset_password', 'Reset password'))

@section('content')
    <div class="relative flex min-h-[70vh] items-center justify-center px-4 py-14">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-cg-red/15 to-transparent"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-7 text-center">
                <a href="{{ route('home') }}" class="inline-flex justify-center"><x-logo :size="44" /></a>
                <h1 class="mt-5 font-display text-3xl tracking-wide text-cg-white">{{ ui('forgot_password', 'Forgot your password?') }}</h1>
                <p class="mt-1.5 text-sm text-cg-muted">
                    {{ ui('forgot_sub', 'Enter your email and we will send you a reset link.') }}
                </p>
            </div>

            <form action="{{ route('password.email') }}" method="POST" class="card space-y-4 p-6 sm:p-7">
                @csrf

                <div>
                    <label class="label" for="forgot-email">{{ ui('password_reset_email', 'Email') }}</label>
                    <input id="forgot-email" type="email" name="email" value="{{ old('email') }}" class="input" autocomplete="email" required maxlength="160">
                </div>

                <button type="submit" class="btn-primary btn-lg btn-block">
                    {{ ui('send_reset_link', 'Send reset link') }}
                </button>

                <p class="text-center text-sm text-cg-muted">
                    <a href="{{ route('login') }}" class="font-medium text-cg-red hover:underline">
                        ← {{ ui('login_now', 'Log in') }}
                    </a>
                </p>
            </form>
        </div>
    </div>
@endsection
