@php($navLinks = [
    ['route' => 'home', 'label' => ui('home', 'Home'), 'match' => 'home'],
    ['route' => 'movies.index', 'label' => ui('movies', 'Movies'), 'match' => 'movies.*'],
    ['route' => 'cinemas.index', 'label' => ui('cinemas', 'Cinemas'), 'match' => 'cinemas.*'],
    ['route' => 'offers.index', 'label' => ui('offers', 'Offers'), 'match' => 'offers.*'],
    ['route' => 'pages.about', 'label' => ui('about', 'About Us'), 'match' => 'pages.about'],
    ['route' => 'pages.contact', 'label' => ui('contact', 'Contact'), 'match' => 'pages.contact'],
])
)

{{-- Announcement bar --}}
@if ($announcements->isNotEmpty())
    <div class="relative z-40 overflow-hidden border-b border-cg-red/30 bg-gradient-to-r from-cg-red/25 via-cg-red/10 to-cg-red/25">
        <div class="flex whitespace-nowrap py-2 text-xs font-medium text-cg-white sm:text-sm">
            @if ($announcements->count() === 1)
                <span class="mx-auto px-4 text-center">
                    @if ($announcements->first()->link)
                        <a href="{{ $announcements->first()->link }}" class="hover:underline">{{ $announcements->first()->localised_message() }}</a>
                    @else
                        {{ $announcements->first()->localised_message() }}
                    @endif
                </span>
            @else
                <div class="cg-marquee-track flex shrink-0 items-center" style="--marquee-shift: -50%;">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach ($announcements as $announcement)
                            <span class="mx-6 inline-flex items-center gap-2">
                                <x-icon name="zap" :size="14" class="text-cg-red" />
                                @if ($announcement->link)
                                    <a href="{{ $announcement->link }}" class="transition hover:text-cg-red">{{ $announcement->localised_message() }}</a>
                                @else
                                    {{ $announcement->localised_message() }}
                                @endif
                            </span>
                        @endforeach
                    @endfor
                </div>
            @endif
        </div>
    </div>
@endif

{{-- Sticky header --}}
<header class="sticky top-0 z-40 border-b border-cg-border-soft bg-cg-black/85 backdrop-blur-md transition">
    <div class="container-page flex h-16 items-center justify-between gap-3 sm:h-[72px]">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="shrink-0" aria-label="CineGo home">
            <x-logo :size="34" class="sm:hidden" />
            <x-logo :size="38" class="hidden sm:inline-flex" />
        </a>

        {{-- Desktop navigation --}}
        <nav class="hidden items-center gap-7 lg:flex" aria-label="Main">
            @foreach ($navLinks as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="nav-link {{ request()->routeIs($link['match']) ? 'nav-link-active' : '' }}"
                >{{ $link['label'] }}</a>
            @endforeach
        </nav>

        {{-- Right side --}}
        <div class="flex items-center gap-2 sm:gap-3">
            {{-- City selector --}}
            <form action="{{ route('city.set') }}" method="POST" data-autosubmit class="hidden md:block">
                @csrf
                <label class="sr-only" for="header-city">{{ ui('select_city', 'Select City') }}</label>
                <div class="relative">
                    <x-icon name="map-pin" :size="15" class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-cg-red" />
                    <select
                        id="header-city"
                        name="city_id"
                        class="h-10 max-w-40 cursor-pointer appearance-none rounded-lg border border-cg-border bg-cg-charcoal/90 py-1 pl-8 pr-7 text-xs text-cg-white transition hover:border-cg-red focus:border-cg-red focus:outline-none sm:max-w-44"
                    >
                        <option value="">{{ ui('all_cities', 'All Cities') }}</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected($currentCity?->id === $city->id)>
                                {{ $city->display_name() }}
                            </option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" :size="13" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-cg-dim" />
                </div>
            </form>

            {{-- Language selector --}}
            <form action="{{ route('locale.set') }}" method="POST" class="hidden sm:block">
                @csrf
                <div class="flex h-10 items-center rounded-lg border border-cg-border bg-cg-charcoal/90 p-1 text-xs font-semibold">
                    <button
                        type="submit"
                        name="locale"
                        value="en"
                        class="rounded-md px-2.5 py-1.5 transition {{ ($currentLocale ?? 'en') === 'en' ? 'bg-cg-red text-white' : 'text-cg-muted hover:text-white' }}"
                    >EN</button>
                    <button
                        type="submit"
                        name="locale"
                        value="np"
                        class="rounded-md px-2.5 py-1.5 transition {{ ($currentLocale ?? 'en') === 'np' ? 'bg-cg-red text-white' : 'text-cg-muted hover:text-white' }}"
                    >नेपाली</button>
                </div>
            </form>

            {{-- Account --}}
            @auth
                <div class="relative hidden lg:block" data-dropdown>
                    <button
                        type="button"
                        data-toggle="account-menu"
                        aria-expanded="false"
                        class="flex h-10 items-center gap-2 rounded-lg border border-cg-border bg-cg-charcoal/90 px-3 text-sm font-medium text-cg-white transition hover:border-cg-red"
                    >
                        <x-icon name="user" :size="16" class="text-cg-red" />
                        <span class="max-w-28 truncate">{{ auth()->user()->name }}</span>
                        <x-icon name="chevron-down" :size="14" class="text-cg-dim" />
                    </button>

                    <div id="account-menu" data-dropdown-menu class="cg-dropdown hidden">
                        <a href="{{ route('account.dashboard') }}" class="cg-dropdown-item flex items-center gap-2.5">
                            <x-icon name="dashboard" :size="16" /> {{ ui('dashboard', 'Dashboard') }}
                        </a>
                        <a href="{{ route('account.bookings') }}" class="cg-dropdown-item flex items-center gap-2.5">
                            <x-icon name="ticket" :size="16" /> {{ ui('my_bookings', 'My Bookings') }}
                        </a>
                        <a href="{{ route('account.favorites') }}" class="cg-dropdown-item flex items-center gap-2.5">
                            <x-icon name="heart" :size="16" /> {{ ui('favorite_movies', 'Favourite movies') }}
                        </a>
                        <a href="{{ route('account.notifications') }}" class="cg-dropdown-item flex items-center gap-2.5">
                            <x-icon name="bell" :size="16" /> {{ ui('notifications', 'Notifications') }}
                        </a>
                        <a href="{{ route('account.settings') }}" class="cg-dropdown-item flex items-center gap-2.5">
                            <x-icon name="settings" :size="16" /> {{ ui('account_settings', 'Account settings') }}
                        </a>
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="cg-dropdown-item flex items-center gap-2.5 border-t border-cg-border text-cg-red">
                                <x-icon name="zap" :size="16" /> {{ ui('admin_panel', 'Admin panel') }}
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="cg-dropdown-item flex w-full items-center gap-2.5 border-t border-cg-border">
                                <x-icon name="log-out" :size="16" /> {{ ui('logout', 'Log out') }}
                            </button>
                        </form>
                    </div>
                </div>

                <a href="{{ route('account.dashboard') }}" class="btn-primary hidden h-10 px-4 lg:hidden sm:inline-flex">
                    <x-icon name="user" :size="16" /> {{ ui('my_account', 'My Account') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost hidden h-10 px-3 sm:inline-flex">
                    {{ ui('login', 'Login') }}
                </a>
                <a href="{{ route('register') }}" class="btn-primary hidden h-10 px-4 sm:inline-flex">
                    {{ ui('register', 'Register') }}
                </a>
            @endauth

            {{-- Mobile menu toggle --}}
            <button
                type="button"
                data-toggle="mobile-menu"
                aria-expanded="false"
                aria-label="{{ ui('menu', 'Menu') }}"
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-cg-border bg-cg-charcoal/90 text-cg-white transition hover:border-cg-red lg:hidden"
            >
                <x-icon name="menu" :size="20" />
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="mobile-menu" class="hidden border-t border-cg-border-soft bg-cg-black/95 lg:hidden">
        <nav class="container-page flex flex-col gap-1 py-4" aria-label="Mobile">
            @foreach ($navLinks as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="rounded-lg px-3 py-3 text-base font-medium transition {{ request()->routeIs($link['match']) ? 'bg-cg-red/15 text-cg-white' : 'text-cg-muted hover:bg-white/5 hover:text-white' }}"
                >{{ $link['label'] }}</a>
            @endforeach

            <div class="mt-3 flex flex-wrap items-center gap-3 border-t border-cg-border-soft pt-4">
                <form action="{{ route('city.set') }}" method="POST" data-autosubmit class="flex-1">
                    @csrf
                    <label class="sr-only" for="mobile-city">{{ ui('select_city', 'Select City') }}</label>
                    <select
                        id="mobile-city"
                        name="city_id"
                        class="input h-11 min-h-11"
                    >
                        <option value="">{{ ui('all_cities', 'All Cities') }}</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected($currentCity?->id === $city->id)>
                                {{ $city->display_name() }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <form action="{{ route('locale.set') }}" method="POST">
                    @csrf
                    <div class="flex h-11 items-center rounded-lg border border-cg-border bg-cg-charcoal p-1 text-xs font-semibold">
                        <button type="submit" name="locale" value="en" class="rounded-md px-3 py-2 transition {{ ($currentLocale ?? 'en') === 'en' ? 'bg-cg-red text-white' : 'text-cg-muted' }}">EN</button>
                        <button type="submit" name="locale" value="np" class="rounded-md px-3 py-2 transition {{ ($currentLocale ?? 'en') === 'np' ? 'bg-cg-red text-white' : 'text-cg-muted' }}">नेपाली</button>
                    </div>
                </form>
            </div>

            <div class="mt-4 flex flex-col gap-2 border-t border-cg-border-soft pt-4">
                @auth
                    <a href="{{ route('account.dashboard') }}" class="btn-outline btn-block justify-start">
                        <x-icon name="user" :size="16" /> {{ ui('my_account', 'My Account') }}
                    </a>
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="btn-ghost btn-block justify-start text-cg-red">
                            <x-icon name="zap" :size="16" /> {{ ui('admin_panel', 'Admin panel') }}
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-ghost btn-block justify-start">
                            <x-icon name="log-out" :size="16" /> {{ ui('logout', 'Log out') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-outline btn-block">{{ ui('login', 'Login') }}</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-block">{{ ui('register', 'Register') }}</a>
                @endauth
            </div>
        </nav>
    </div>
</header>
