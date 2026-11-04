<!DOCTYPE html>
<html lang="{{ ($currentLocale ?? 'en') === 'np' ? 'ne' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') · CineGo Admin@elseCineGo Admin@endif</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#090909">

    <link rel="icon" href="{{ asset('img/icons/icon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
</head>
<body class="flex min-h-screen flex-col bg-cg-black">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside
            id="admin-sidebar"
            class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-cg-border-soft bg-cg-black/95 backdrop-blur transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
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

            <nav class="flex flex-col gap-1 p-3" aria-label="Admin">
                @php
                    $adminLinks = [
                        ['route' => 'admin.dashboard', 'label' => ui('dashboard', 'Dashboard'), 'icon' => 'dashboard', 'match' => 'admin.dashboard'],
                        ['route' => 'admin.bookings.index', 'label' => ui('my_bookings', 'Bookings'), 'icon' => 'ticket', 'match' => 'admin.bookings.*'],
                        ['route' => 'admin.movies.index', 'label' => ui('movies', 'Movies'), 'icon' => 'film', 'match' => 'admin.movies.*'],
                        ['route' => 'admin.cinemas.index', 'label' => ui('cinemas', 'Cinemas'), 'icon' => 'armchair', 'match' => 'admin.cinemas.*'],
                        ['route' => 'admin.showtimes.index', 'label' => ui('showtimes_for', 'Showtimes'), 'icon' => 'clock', 'match' => 'admin.showtimes.*'],
                        ['route' => 'admin.offers.index', 'label' => ui('offers', 'Offers'), 'icon' => 'tag', 'match' => 'admin.offers.*'],
                        ['route' => 'admin.announcements.index', 'label' => ui('announcements', 'Announcements'), 'icon' => 'bell', 'match' => 'admin.announcements.*'],
                        ['route' => 'admin.messages.index', 'label' => ui('contact', 'Messages'), 'icon' => 'mail', 'match' => 'admin.messages.*'],
                        ['route' => 'admin.users.index', 'label' => ui('customers', 'Customers'), 'icon' => 'users', 'match' => 'admin.users.*'],
                    ];
                @endphp

                @foreach ($adminLinks as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium transition
                            {{ request()->routeIs($link['match']) ? 'bg-cg-red/15 text-cg-white' : 'text-cg-muted hover:bg-white/5 hover:text-white' }}"
                    >
                        <x-icon :name="$link['icon']" :size="17" class="{{ request()->routeIs($link['match']) ? 'text-cg-red' : '' }}" />
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-4 border-t border-cg-border-soft p-3">
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium text-cg-muted transition hover:bg-white/5 hover:text-white">
                    <x-icon name="eye" :size="17" /> {{ ui('view_site', 'View site') }}
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3.5 py-2.5 text-sm font-medium text-cg-muted transition hover:bg-white/5 hover:text-white">
                        <x-icon name="log-out" :size="17" /> {{ ui('logout', 'Log out') }}
                    </button>
                </form>
            </div>
        </aside>

        <div id="admin-overlay" data-admin-toggle class="fixed inset-0 z-30 hidden bg-black/60 lg:hidden"></div>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-3 border-b border-cg-border-soft bg-cg-black/85 px-4 backdrop-blur sm:px-6">
                <button
                    type="button"
                    data-admin-toggle
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-cg-border bg-cg-charcoal text-cg-white lg:hidden"
                    aria-label="{{ ui('menu', 'Menu') }}"
                >
                    <x-icon name="menu" :size="19" />
                </button>

                <h1 class="truncate font-semibold text-cg-white">@yield('title', ui('admin_panel', 'Admin panel'))</h1>

                <div class="flex items-center gap-3">
                    <span class="hidden text-sm text-cg-muted sm:block">{{ auth()->user()->name }}</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-cg-red/20 text-sm font-bold text-cg-red">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (function () {
            var sidebar = document.getElementById('admin-sidebar');
            var overlay = document.getElementById('admin-overlay');
            if (!sidebar) return;

            document.querySelectorAll('[data-admin-toggle]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var open = !sidebar.classList.contains('-translate-x-full');
                    sidebar.classList.toggle('-translate-x-full', open);
                    overlay.classList.toggle('hidden', open);
                });
            });
        })();
    </script>
</body>
</html>
