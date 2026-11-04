@extends('layouts.admin')

@section('title', ui('settings', 'Settings'))

@section('content')
    <div class="max-w-3xl space-y-6">
        <section class="card p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cg-red/15 text-cg-red">
                    <x-icon name="settings" :size="22" />
                </span>
                <div>
                    <h2 class="font-semibold text-cg-white">{{ ui('admin_settings', 'Admin settings') }}</h2>
                    <p class="mt-1 text-sm text-cg-muted">{{ ui('admin_settings_subtitle', 'Manage your account preferences and CineGo administration tools.') }}</p>
                </div>
            </div>
        </section>

        <section class="card divide-y divide-cg-border-soft">
            <a href="{{ route('account.profile') }}" class="flex items-center gap-4 p-5 transition hover:bg-white/[0.03]">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-cg-charcoal text-cg-red"><x-icon name="user" :size="18" /></span>
                <span class="min-w-0 flex-1">
                    <span class="block font-medium text-cg-white">{{ ui('profile_settings', 'Profile settings') }}</span>
                    <span class="block text-sm text-cg-muted">{{ ui('profile_settings_subtitle', 'Update your name, email and contact details.') }}</span>
                </span>
                <x-icon name="chevron-right" :size="17" class="text-cg-dim" />
            </a>
            <a href="{{ route('account.settings') }}" class="flex items-center gap-4 p-5 transition hover:bg-white/[0.03]">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-cg-charcoal text-cg-red"><x-icon name="lock" :size="18" /></span>
                <span class="min-w-0 flex-1">
                    <span class="block font-medium text-cg-white">{{ ui('security_settings', 'Security and preferences') }}</span>
                    <span class="block text-sm text-cg-muted">{{ ui('security_settings_subtitle', 'Change your password, language and city preferences.') }}</span>
                </span>
                <x-icon name="chevron-right" :size="17" class="text-cg-dim" />
            </a>
        </section>
    </div>
@endsection
