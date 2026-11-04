<footer class="mt-16 border-t border-cg-border-soft bg-gradient-to-b from-transparent to-black/60">
    <div class="container-page grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Brand --}}
        <div class="space-y-4">
            <x-logo :size="36" />
            <p class="max-w-xs text-sm leading-relaxed text-cg-muted">
                {{ ui('footer_about_text', 'CineGo is a modern Nepali movie ticket booking platform — discover films, choose your hall and lock your seats in under a minute.') }}
            </p>
            <div class="flex items-center gap-2">
                <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-cg-border bg-cg-charcoal text-cg-muted transition hover:border-cg-red hover:text-white">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5H16.7V4.6c-.3 0-1.25-.1-2.35-.1-2.3 0-3.9 1.4-3.9 4v2.4H7.8V14h2.65v8z"/></svg>
                </a>
                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-cg-border bg-cg-charcoal text-cg-muted transition hover:border-cg-red hover:text-white">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                <a href="https://x.com" target="_blank" rel="noopener noreferrer" aria-label="X"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-cg-border bg-cg-charcoal text-cg-muted transition hover:border-cg-red hover:text-white">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.65l-5.22-6.82-5.97 6.82H1.67l7.73-8.84L1.25 2.25h6.82l4.71 6.23zm-1.16 17.52h1.83L7.08 4.13H5.11z"/></svg>
                </a>
            </div>
        </div>

        {{-- Quick links --}}
        <nav aria-label="{{ ui('quick_links', 'Quick links') }}">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-cg-white">{{ ui('quick_links', 'Quick links') }}</h3>
            <ul class="space-y-2.5 text-sm">
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('home') }}">{{ ui('home', 'Home') }}</a></li>
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('movies.index') }}">{{ ui('movies', 'Movies') }}</a></li>
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('cinemas.index') }}">{{ ui('cinemas', 'Cinemas') }}</a></li>
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('offers.index') }}">{{ ui('offers', 'Offers') }}</a></li>
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('pages.about') }}">{{ ui('about', 'About Us') }}</a></li>
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('pages.contact') }}">{{ ui('contact', 'Contact') }}</a></li>
            </ul>
        </nav>

        {{-- Help & support --}}
        <nav aria-label="{{ ui('help_support', 'Help & support') }}">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-cg-white">{{ ui('help_support', 'Help & support') }}</h3>
            <ul class="space-y-2.5 text-sm">
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('pages.terms') }}">{{ ui('terms', 'Terms & Conditions') }}</a></li>
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('pages.privacy') }}">{{ ui('privacy', 'Privacy Policy') }}</a></li>
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('pages.refund') }}">{{ ui('refund', 'Refund Policy') }}</a></li>
                <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('pages.contact') }}">{{ ui('faq', 'FAQs') }}</a></li>
                @guest
                    <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('login') }}">{{ ui('login', 'Login') }}</a></li>
                    <li><a class="text-cg-muted transition hover:text-cg-red" href="{{ route('register') }}">{{ ui('register', 'Register') }}</a></li>
                @endguest
            </ul>
        </nav>

        {{-- Contact --}}
        <div>
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-cg-white">{{ ui('contact_details', 'Contact details') }}</h3>
            <ul class="space-y-3.5 text-sm text-cg-muted">
                <li class="flex items-start gap-2.5">
                    <x-icon name="map-pin" :size="16" class="mt-0.5 text-cg-red" />
                    <span>Kawasoti-3, Nepal</span>
                </li>
                <li class="flex items-center gap-2.5">
                    <x-icon name="phone" :size="16" class="text-cg-red" />
                    <a href="tel:+9779800000000" class="transition hover:text-white">+977-9800-000-000</a>
                </li>
                <li class="flex items-center gap-2.5">
                    <x-icon name="mail" :size="16" class="text-cg-red" />
                    <a href="mailto:support@cinego.com.np" class="transition hover:text-white">support@cinego.com.np</a>
                </li>
                <li class="flex items-center gap-2.5">
                    <x-icon name="clock" :size="16" class="text-cg-red" />
                    <span>{{ ui('office_hours', 'Office hours') }}: 9:00 – 21:00</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="border-t border-cg-border-soft">
        <div class="container-page flex flex-col items-center justify-between gap-3 py-5 text-xs text-cg-dim sm:flex-row">
            <p>© {{ date('Y') }} CineGo. {{ ui('rights', 'All rights reserved.') }}</p>
            <p>{{ ui('crafted', 'Crafted for Nepali cinema lovers.') }}</p>
        </div>
    </div>
</footer>
