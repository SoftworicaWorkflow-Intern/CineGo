<footer class="mt-16 border-t border-cg-border-soft bg-[#FFF1F1] shadow-[0_-8px_32px_rgba(23,23,23,0.04)]">
    <div class="container-page pt-10 sm:pt-14">
        <section class="grid gap-6 rounded-2xl border border-cg-red/10 bg-white p-6 shadow-sm sm:p-8 lg:grid-cols-[1fr_auto] lg:items-center">
            <div class="max-w-xl">
                <h2 class="text-xl font-bold tracking-tight text-cg-white sm:text-2xl">{{ ui('newsletter_title', 'Stay Updated with CineGo') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-cg-muted">{{ ui('newsletter_description', 'Sign up to hear about upcoming movie releases and exclusive offers from CineGo.') }}</p>
            </div>

            <form action="{{ route('newsletter-subscriptions.store') }}" method="POST" class="w-full lg:min-w-[26rem]">
                @csrf
                <label for="newsletter-email" class="sr-only">{{ ui('newsletter_email', 'Email address') }}</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input
                        id="newsletter-email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        maxlength="254"
                        required
                        placeholder="{{ ui('newsletter_email', 'Email address') }}"
                        class="input min-w-0 flex-1 @error('email') border-cg-red @enderror"
                        @error('email') aria-invalid="true" @enderror
                    >
                    <button type="submit" class="btn-primary shrink-0">
                        {{ ui('newsletter_subscribe', 'Subscribe') }}
                        <x-icon name="arrow-right" :size="16" />
                    </button>
                </div>
            </form>
        </section>
    </div>

    <div class="container-page grid gap-10 py-12 sm:grid-cols-2 sm:gap-x-8 sm:gap-y-12 lg:grid-cols-[1.25fr_0.9fr_1.2fr_1fr] lg:gap-10 lg:py-14">
        <div class="space-y-5">
            <a href="{{ route('home') }}" class="inline-flex rounded-sm focus-visible:outline">
                <x-logo :size="46" />
            </a>
            <p class="max-w-xs text-sm leading-relaxed text-cg-muted">
                {{ ui('footer_about_text', 'CineGo is a modern Nepali movie ticket booking platform — discover films, choose your hall and lock your seats in under a minute.') }}
            </p>
            <p class="font-semibold text-cg-white">{{ ui('footer_tagline', 'Your Movie. Your Moment. Your CineGo.') }}</p>
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-cg-muted">{{ ui('follow_us', 'Follow us') }}</p>
                <div class="flex items-center gap-2">
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-cg-border bg-white text-cg-muted transition duration-200 hover:-translate-y-0.5 hover:border-cg-red hover:bg-cg-red hover:text-white">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5H16.7V4.6c-.3 0-1.25-.1-2.35-.1-2.3 0-3.9 1.4-3.9 4v2.4H7.8V14h2.65v8z"/></svg>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-cg-border bg-white text-cg-muted transition duration-200 hover:-translate-y-0.5 hover:border-cg-red hover:bg-cg-red hover:text-white">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" aria-label="TikTok"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-cg-border bg-white text-cg-muted transition duration-200 hover:-translate-y-0.5 hover:border-cg-red hover:bg-cg-red hover:text-white">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.6 7.2a5.8 5.8 0 0 1-3.6-1.3v7.3a5.8 5.8 0 1 1-5.8-5.8c.4 0 .8 0 1.2.1v3.2a2.6 2.6 0 1 0 1.4 2.3V2.5h3.2a5.8 5.8 0 0 0 3.6 3.7z"/></svg>
                    </a>
                    <a href="https://x.com" target="_blank" rel="noopener noreferrer" aria-label="X"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-cg-border bg-white text-cg-muted transition duration-200 hover:-translate-y-0.5 hover:border-cg-red hover:bg-cg-red hover:text-white">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.65l-5.22-6.82-5.97 6.82H1.67l7.73-8.84L1.25 2.25h6.82l4.71 6.23zm-1.16 17.52h1.83L7.08 4.13H5.11z"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <nav aria-label="{{ ui('quick_links', 'Quick links') }}">
            <h2 class="mb-5 text-sm font-bold uppercase tracking-wider text-cg-white">{{ ui('quick_links', 'Quick links') }}</h2>
            <ul class="space-y-3 text-sm">
                <li><a class="footer-link" href="{{ route('home') }}">{{ ui('home', 'Home') }}</a></li>
                <li><a class="footer-link" href="{{ route('movies.index') }}">{{ ui('movies', 'Movies') }}</a></li>
                <li><a class="footer-link" href="{{ route('movies.index', ['tab' => 'now_showing']) }}">{{ ui('now_showing_link', 'Now Showing') }}</a></li>
                <li><a class="footer-link" href="{{ route('movies.index', ['tab' => 'coming_soon']) }}">{{ ui('coming_soon_link', 'Coming Soon') }}</a></li>
                <li><a class="footer-link" href="{{ route('pages.about') }}">{{ ui('about', 'About Us') }}</a></li>
                <li><a class="footer-link" href="{{ route('pages.contact') }}">{{ ui('contact', 'Contact') }}</a></li>
            </ul>
        </nav>

        <nav aria-label="{{ ui('help_support', 'Help & support') }}">
            <h2 class="mb-5 text-sm font-bold uppercase tracking-wider text-cg-white">{{ ui('help_support', 'Help & support') }}</h2>
            <ul class="space-y-3 text-sm">
                <li><a class="footer-link" href="{{ route('pages.contact') }}">{{ ui('faq', 'FAQs') }}</a></li>
                <li><a class="footer-link" href="{{ route('pages.terms') }}">{{ ui('terms', 'Terms & Conditions') }}</a></li>
                <li><a class="footer-link" href="{{ route('pages.privacy') }}">{{ ui('privacy', 'Privacy Policy') }}</a></li>
                <li><a class="footer-link" href="{{ route('pages.refund') }}">{{ ui('refund', 'Refund Policy') }}</a></li>
                <li><a class="footer-link" href="{{ route('pages.contact') }}">{{ ui('contact_support', 'Contact Support') }}</a></li>
                @guest
                    <li><a class="footer-link" href="{{ route('login') }}">{{ ui('login', 'Login') }}</a></li>
                    <li><a class="footer-link" href="{{ route('register') }}">{{ ui('register', 'Register') }}</a></li>
                @endguest
            </ul>
        </nav>

        <div>
            <h2 class="mb-5 text-sm font-bold uppercase tracking-wider text-cg-white">{{ ui('contact_details', 'Contact details') }}</h2>
            <ul class="space-y-4 text-sm text-cg-muted">
                <li class="flex items-start gap-3">
                    <x-icon name="map-pin" :size="18" class="mt-0.5 text-cg-red" />
                    <span>Kawasoti-3, Nepal</span>
                </li>
                <li class="flex items-start gap-3">
                    <x-icon name="phone" :size="18" class="mt-0.5 text-cg-red" />
                    <a href="tel:+9779800000000" class="footer-link">+977-9800-000-000</a>
                </li>
                <li class="flex items-start gap-3">
                    <x-icon name="mail" :size="18" class="mt-0.5 text-cg-red" />
                    <a href="mailto:support@cinego.com.np" class="footer-link break-all">support@cinego.com.np</a>
                </li>
                <li class="flex items-start gap-3">
                    <x-icon name="clock" :size="18" class="mt-0.5 text-cg-red" />
                    <span>{{ ui('office_hours', 'Office hours') }}: 9:00 – 21:00</span>
                </li>
            </ul>
            <a
                href="https://www.google.com/maps/search/?api=1&query=Kawasoti-3%2C%20Nepal"
                target="_blank"
                rel="noopener noreferrer"
                class="btn-outline mt-6 w-full justify-center px-4"
            >
                <x-icon name="map-pin" :size="16" /> {{ ui('directions', 'Get directions') }}
            </a>
        </div>
    </div>

    <div class="border-t border-cg-border">
        <div class="container-page flex flex-col gap-4 py-6 text-sm text-cg-muted md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <p>© {{ date('Y') }} CineGo. {{ ui('rights', 'All rights reserved.') }}</p>
            </div>
            <div class="flex flex-col gap-3">
                <nav aria-label="{{ ui('legal_links', 'Legal links') }}" class="flex flex-wrap gap-x-5 gap-y-2">
                    <a class="footer-link" href="{{ route('pages.privacy') }}">{{ ui('privacy', 'Privacy Policy') }}</a>
                    <a class="footer-link" href="{{ route('pages.terms') }}">{{ ui('terms', 'Terms & Conditions') }}</a>
                    <a class="footer-link" href="{{ route('pages.refund') }}">{{ ui('refund', 'Refund Policy') }}</a>
                </nav>
                <div class="flex items-center gap-3 text-xs text-cg-muted md:translate-x-4">
                    <span>Designed &amp; developed by</span>
                    <img
                        src="{{ asset('img/softworica.png') }}"
                        alt="Softworica"
                        width="140"
                        height="28"
                        loading="lazy"
                    >
                </div>
            </div>
        </div>
    </div>
</footer>
