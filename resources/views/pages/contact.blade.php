@extends('layouts.app')

@section('title', ui('contact_title', 'Contact us'))

@section('content')
    <div class="container-page py-10">
        <x-breadcrumbs :items="[['label' => ui('contact', 'Contact')]]" />

        <div class="mb-8">
            <p class="section-kicker">{{ ui('contact', 'Contact') }}</p>
            <h1 class="section-title">{{ ui('contact_title', 'Contact us') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-cg-muted sm:text-base">
                {{ ui('contact_sub', 'Questions about a booking? We usually reply within a day.') }}
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            {{-- Form --}}
            <form action="{{ route('pages.contact.store') }}" method="POST" class="card space-y-4 p-5 sm:p-7">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="contact-name">{{ ui('name', 'Name') }} *</label>
                        <input id="contact-name" type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" class="input" required maxlength="100">
                    </div>
                    <div>
                        <label class="label" for="contact-email">{{ ui('email_address', 'Email address') }} *</label>
                        <input id="contact-email" type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" class="input" required maxlength="160">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="contact-phone">{{ ui('mobile_number', 'Mobile number') }}</label>
                        <input id="contact-phone" type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" class="input" maxlength="30" placeholder="98XXXXXXXX">
                    </div>
                    <div>
                        <label class="label" for="contact-subject">{{ ui('subject', 'Subject') }} *</label>
                        <input id="contact-subject" type="text" name="subject" value="{{ old('subject') }}" class="input" required maxlength="160">
                    </div>
                </div>

                <div>
                    <label class="label" for="contact-message">{{ ui('message', 'Message') }} *</label>
                    <textarea id="contact-message" name="message" rows="6" class="input resize-y" required maxlength="3000">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="btn-primary btn-lg">
                    <x-icon name="send" :size="17" /> {{ ui('send_message', 'Send message') }}
                </button>
            </form>

            {{-- Contact details --}}
            <aside class="space-y-5">
                <div class="card space-y-4 p-5">
                    <h2 class="font-semibold text-cg-white">{{ ui('contact_details', 'Contact details') }}</h2>

                    <a href="tel:+9779800000000" class="flex items-start gap-3 rounded-lg border border-cg-border bg-cg-charcoal p-3.5 transition hover:border-cg-red">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cg-red/15 text-cg-red">
                            <x-icon name="phone" :size="17" />
                        </span>
                        <span>
                            <span class="block text-sm font-medium text-cg-white">+977-9800-000-000</span>
                            <span class="text-xs text-cg-dim">{{ ui('office_hours', 'Office hours') }}: 9:00 – 21:00</span>
                        </span>
                    </a>

                    <a href="mailto:support@cinego.com.np" class="flex items-start gap-3 rounded-lg border border-cg-border bg-cg-charcoal p-3.5 transition hover:border-cg-red">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cg-red/15 text-cg-red">
                            <x-icon name="mail" :size="17" />
                        </span>
                        <span>
                            <span class="block break-all text-sm font-medium text-cg-white">support@cinego.com.np</span>
                            <span class="text-xs text-cg-dim">{{ ui('reply_time', 'Replies within 24 hours') }}</span>
                        </span>
                    </a>

                    <div class="flex items-start gap-3 rounded-lg border border-cg-border bg-cg-charcoal p-3.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cg-red/15 text-cg-red">
                            <x-icon name="map-pin" :size="17" />
                        </span>
                        <span>
                            <span class="block text-sm font-medium text-cg-white">{{ ui('find_us', 'Find us') }}</span>
                            <span class="text-xs text-cg-dim">Kawasoti-3, Nepal</span>
                        </span>
                    </div>
                </div>

                <div class="card p-5">
                    <h2 class="mb-3 font-semibold text-cg-white">{{ ui('quick_links', 'Quick links') }}</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('pages.terms') }}" class="text-cg-muted transition hover:text-cg-red">{{ ui('terms', 'Terms & Conditions') }}</a></li>
                        <li><a href="{{ route('pages.privacy') }}" class="text-cg-muted transition hover:text-cg-red">{{ ui('privacy', 'Privacy Policy') }}</a></li>
                        <li><a href="{{ route('pages.refund') }}" class="text-cg-muted transition hover:text-cg-red">{{ ui('refund', 'Refund Policy') }}</a></li>
                    </ul>
                </div>
            </aside>
        </div>

        <div class="card mt-8 overflow-hidden">
            <iframe
                src="https://maps.google.com/maps?q=Kawasoti-3%2C%20Nepal&output=embed"
                title="Map showing Kawasoti-3, Nepal"
                class="h-64 w-full border-0 sm:h-80"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
            ></iframe>
            <a
                href="https://www.google.com/maps/search/?api=1&query=Kawasoti-3%2C%20Nepal"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center justify-center gap-2 border-t border-cg-border-soft px-4 py-3 text-sm font-semibold text-cg-red transition hover:bg-cg-red/5"
            >
                <x-icon name="map-pin" :size="16" /> {{ ui('directions', 'Get directions') }}
            </a>
        </div>
    </div>
@endsection
