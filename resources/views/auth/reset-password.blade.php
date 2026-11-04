@extends('layouts.app')

@section('title', ui('reset_password', 'Reset password'))

@section('content')
    <div class="relative flex min-h-[70vh] items-center justify-center px-4 py-14">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-cg-red/15 to-transparent"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-7 text-center">
                <a href="{{ route('home') }}" class="inline-flex justify-center"><x-logo :size="44" /></a>
                <h1 class="mt-5 font-display text-3xl tracking-wide text-cg-white">{{ ui('reset_password', 'Reset password') }}</h1>
                <p class="mt-1.5 text-sm text-cg-muted">{{ ui('reset_sub', 'Choose a new password for your account.') }}</p>
            </div>

            <form action="{{ route('password.update') }}" method="POST" class="card space-y-4 p-6 sm:p-7">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ request()->query('email') }}">

                <div>
                    <label class="label" for="reset-email">{{ ui('password_reset_email', 'Email') }}</label>
                    <input id="reset-email" type="email" name="email" value="{{ request()->query('email') }}" class="input" autocomplete="email" required maxlength="160">
                </div>

                <div>
                    <label class="label" for="reset-password">{{ ui('new_password', 'New password') }}</label>
                    <div class="relative">
                        <input id="reset-password" type="password" name="password" class="input pr-11" autocomplete="new-password" required>
                        <button type="button" data-password-toggle="reset-password" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-cg-dim hover:text-cg-white" aria-label="{{ ui('show_password', 'Show password') }}"><x-icon name="eye" :size="17" /></button>
                    </div>
                    <p class="mt-1.5 text-xs text-cg-dim">{{ ui('password_hint', 'At least 8 characters.') }}</p>
                </div>

                <div>
                    <label class="label" for="reset-password-confirmation">{{ ui('confirm_password', 'Confirm password') }}</label>
                    <div class="relative">
                        <input id="reset-password-confirmation" type="password" name="password_confirmation" class="input pr-11" autocomplete="new-password" required>
                        <button type="button" data-password-toggle="reset-password-confirmation" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-cg-dim hover:text-cg-white" aria-label="{{ ui('show_password', 'Show password') }}"><x-icon name="eye" :size="17" /></button>
                    </div>
                </div>

                <button type="submit" class="btn-primary btn-lg btn-block">{{ ui('reset_password', 'Reset password') }}</button>
            </form>
        </div>
    </div>
@endsection
