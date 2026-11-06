<!DOCTYPE html>
<html lang="{{ ($currentLocale ?? 'en') === 'np' ? 'ne' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') · CineGo Admin @else CineGo Admin @endif</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#ffffff">

    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="96x96">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
</head>
<body class="flex min-h-screen flex-col bg-cg-black">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside
            id="admin-sidebar"
            class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto border-r border-cg-border-soft bg-cg-black/95 backdrop-blur transition-all lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
        >
            <div class="flex h-16 items-center justify-between border-b border-cg-border-soft px-5">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <x-logo :size="30" />
                </a>
                <button
                    type="button"
                    data-admin-toggle
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-cg-border text-cg-muted lg:hidden"
                    aria-label="{{ ui('close', 'Close') }}"
                >
                    <x-icon name="x" :size="17" />
                </button>
            </div>

            <nav class="flex flex-col gap-1 p-3 pb-20" aria-label="Admin">
                @php
                    $adminLinks = [
                        ['route' => 'admin.dashboard', 'label' => ui('dashboard', 'Dashboard'), 'icon' => 'dashboard', 'match' => 'admin.dashboard'],
                        ['route' => 'admin.movies.index', 'label' => ui('movies', 'Movies'), 'icon' => 'film', 'match' => 'admin.movies.*'],
                        ['route' => 'admin.showtimes.index', 'label' => ui('showtimes_for', 'Showtime'), 'icon' => 'clock', 'match' => 'admin.showtimes.*'],
                        ['route' => 'admin.bookings.index', 'label' => ui('bookings', 'Bookings'), 'icon' => 'ticket', 'match' => 'admin.bookings.*'],
                        ['route' => 'admin.messages.index', 'label' => ui('contact', 'Messages'), 'icon' => 'mail', 'match' => 'admin.messages.*'],
                        ['route' => 'admin.users.index', 'label' => ui('customers', 'Customers'), 'icon' => 'users', 'match' => 'admin.users.*'],
                        ['route' => 'admin.settings.index', 'label' => ui('settings', 'Settings'), 'icon' => 'settings', 'match' => 'admin.settings.*'],
                    ];
                @endphp

                @foreach ($adminLinks as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        data-sidebar-link
                        class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition
                            {{ request()->routeIs($link['match']) ? 'bg-cg-red/10 text-cg-red' : 'text-cg-muted hover:bg-cg-charcoal hover:text-cg-white' }}"
                    >
                        <x-icon :name="$link['icon']" :size="17" class="{{ request()->routeIs($link['match']) ? 'text-cg-red' : '' }}" />
                        <span data-sidebar-label>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="absolute bottom-0 left-0 w-full border-t border-cg-border-soft bg-cg-black p-3">
                <form action="{{ route('logout') }}" method="POST" data-admin-logout>
                    @csrf
                    <button type="submit" data-sidebar-link class="flex w-full items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium text-cg-muted transition hover:bg-cg-charcoal hover:text-cg-white">
                        <x-icon name="log-out" :size="17" /> <span data-sidebar-label>{{ ui('logout', 'Log out') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <div id="admin-overlay" data-admin-toggle class="fixed inset-0 z-30 hidden bg-black/60 lg:hidden"></div>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="fixed inset-x-0 top-0 z-20 flex h-16 items-center justify-between gap-3 border-b border-cg-border-soft bg-cg-black/90 px-4 backdrop-blur transition-[left] lg:left-64 sm:px-6">
                <button
                    type="button"
                    data-admin-sidebar-toggle
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-cg-border bg-cg-charcoal text-cg-white"
                    aria-label="{{ ui('toggle_sidebar', 'Toggle sidebar') }}"
                    aria-expanded="true"
                >
                    <x-icon name="menu" :size="19" />
                </button>

                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <span class="hidden shrink-0 text-sm font-semibold text-cg-red sm:block">{{ ui('admin_panel', 'Admin panel') }}</span>
                    <span class="hidden text-cg-dim sm:block" aria-hidden="true">/</span>
                    <h1 class="truncate font-semibold text-cg-white">@yield('title', ui('admin_panel', 'Admin panel'))</h1>
                    <span id="admin-clock" class="hidden text-sm text-cg-muted md:block" aria-live="polite"></span>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" data-admin-fullscreen class="hidden h-10 w-10 items-center justify-center rounded-lg border border-cg-border bg-cg-charcoal text-cg-muted transition hover:text-cg-white sm:flex" aria-label="{{ ui('fullscreen', 'Toggle fullscreen') }}">
                        <x-icon name="maximize" :size="17" />
                    </button>
                    <span class="hidden text-sm text-cg-muted sm:block">{{ auth()->user()->name }}</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-cg-red/20 text-sm font-bold text-cg-red">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                </div>
            </header>

            <main class="flex-1 px-4 pb-4 pt-20 sm:px-6 sm:pb-6">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    <div id="admin-logout-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="admin-logout-title">
        <div class="w-full max-w-sm rounded-2xl border border-cg-border-soft bg-cg-card p-6 text-cg-white shadow-2xl">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-cg-red/15 text-cg-red">
                <x-icon name="log-out" :size="20" />
            </div>
            <h2 id="admin-logout-title" class="mt-4 text-lg font-semibold">{{ ui('logout_confirm_title', 'Log out of admin panel?') }}</h2>
            <p class="mt-2 text-sm text-cg-muted">{{ ui('logout_confirm_message', 'You will need to log in again to access the admin panel.') }}</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" data-admin-logout-cancel class="btn-outline !border-cg-border !text-cg-white hover:!border-cg-border hover:!bg-cg-charcoal hover:!text-cg-white active:!bg-cg-border active:!text-cg-white">{{ ui('cancel', 'Cancel') }}</button>
                <button type="button" data-admin-logout-confirm class="btn-primary hover:!text-white active:!text-cg-red">{{ ui('logout', 'Log out') }}</button>
            </div>
        </div>
    </div>

    <div id="admin-delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="admin-delete-title">
        <div class="w-full max-w-sm rounded-2xl border border-cg-border-soft bg-cg-card p-6 text-cg-white shadow-2xl">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-cg-red/15 text-cg-red">
                <x-icon name="trash" :size="20" />
            </div>
            <h2 id="admin-delete-title" class="mt-4 text-lg font-semibold">{{ ui('delete_confirm_title', 'Confirm deletion') }}</h2>
            <p data-admin-delete-message class="mt-2 text-sm text-cg-muted"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" data-admin-delete-cancel class="btn-outline !border-cg-border !text-cg-white hover:!border-cg-border hover:!bg-cg-charcoal hover:!text-cg-white active:!bg-cg-border active:!text-cg-white">{{ ui('cancel', 'Cancel') }}</button>
                <button type="button" data-admin-delete-confirm class="btn-primary hover:!text-white active:!text-cg-red">{{ ui('delete', 'Delete') }}</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var sidebar = document.getElementById('admin-sidebar');
            var overlay = document.getElementById('admin-overlay');
            var sidebarToggle = document.querySelector('[data-admin-sidebar-toggle]');
            var header = document.querySelector('header');
            var desktopCollapsed = false;

            if (sidebar) {
                document.querySelectorAll('[data-admin-toggle]').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        var open = !sidebar.classList.contains('-translate-x-full');
                        sidebar.classList.toggle('-translate-x-full', open);
                        overlay.classList.toggle('hidden', open);
                    });
                });
            }

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function () {
                    if (window.matchMedia('(min-width: 1024px)').matches) {
                        desktopCollapsed = !desktopCollapsed;
                        sidebar.classList.toggle('lg:w-20', desktopCollapsed);
                        sidebar.classList.toggle('lg:w-64', !desktopCollapsed);
                        header.classList.toggle('lg:left-20', desktopCollapsed);
                        header.classList.toggle('lg:left-64', !desktopCollapsed);
                        sidebar.querySelectorAll('[data-sidebar-label]').forEach(function (label) {
                            label.classList.toggle('lg:hidden', desktopCollapsed);
                        });
                        sidebar.querySelectorAll('[data-sidebar-link]').forEach(function (link) {
                            link.classList.toggle('lg:justify-center', desktopCollapsed);
                            link.classList.toggle('lg:justify-start', !desktopCollapsed);
                        });
                        sidebarToggle.setAttribute('aria-expanded', String(!desktopCollapsed));
                    } else {
                        var open = sidebar.classList.contains('-translate-x-full');
                        sidebar.classList.toggle('-translate-x-full', !open);
                        overlay.classList.toggle('hidden', !open);
                    }
                });
            }

            var clock = document.getElementById('admin-clock');
            var updateClock = function () {
                if (clock) clock.textContent = new Date().toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
            };
            updateClock();
            window.setInterval(updateClock, 1000);

            var fullscreenButton = document.querySelector('[data-admin-fullscreen]');
            if (fullscreenButton) {
                fullscreenButton.addEventListener('click', function () {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                });
                document.addEventListener('fullscreenchange', function () {
                    fullscreenButton.innerHTML = document.fullscreenElement
                        ? '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3v5H3M21 8h-5V3M16 21v-5h5M3 16h5v5"></path></svg>'
                        : '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H3v5M21 8V3h-5M16 21h5v-5M3 16v5h5"></path></svg>';
                });
            }

            var logoutForm = document.querySelector('[data-admin-logout]');
            var logoutModal = document.getElementById('admin-logout-modal');
            if (logoutForm && logoutModal) {
                logoutForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    logoutModal.classList.remove('hidden');
                    logoutModal.classList.add('flex');
                });
                logoutModal.querySelector('[data-admin-logout-cancel]').addEventListener('click', function () {
                    logoutModal.classList.add('hidden');
                    logoutModal.classList.remove('flex');
                });
                logoutModal.querySelector('[data-admin-logout-confirm]').addEventListener('click', function () {
                    logoutForm.submit();
                });
                logoutModal.addEventListener('click', function (event) {
                    if (event.target === logoutModal) {
                        logoutModal.classList.add('hidden');
                        logoutModal.classList.remove('flex');
                    }
                });
            }
        })();
    </script>
</body>
</html>
