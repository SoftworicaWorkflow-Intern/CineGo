@php
    $links = auth()->user()->is_admin
        ? [
            ['route' => 'account.profile', 'label' => ui('profile_settings', 'Profile settings'), 'icon' => 'user', 'match' => 'account.profile'],
            ['route' => 'account.settings', 'label' => ui('security_settings', 'Security'), 'icon' => 'settings', 'match' => 'account.settings'],
        ]
        : [
            ['route' => 'account.dashboard', 'label' => ui('dashboard', 'Dashboard'), 'icon' => 'dashboard', 'match' => 'account.dashboard'],
            ['route' => 'account.bookings', 'label' => ui('my_bookings', 'My Bookings'), 'icon' => 'ticket', 'match' => 'account.bookings*'],
            ['route' => 'account.favorites', 'label' => ui('favorite_movies', 'Favourite movies'), 'icon' => 'heart', 'match' => 'account.favorites'],
            ['route' => 'account.payments', 'label' => ui('payment_history', 'Payment history'), 'icon' => 'wallet', 'match' => 'account.payments'],
            ['route' => 'account.notifications', 'label' => ui('notifications', 'Notifications'), 'icon' => 'bell', 'match' => 'account.notifications'],
            ['route' => 'account.profile', 'label' => ui('profile_settings', 'Profile settings'), 'icon' => 'user', 'match' => 'account.profile'],
            ['route' => 'account.settings', 'label' => ui('account_settings', 'Account settings'), 'icon' => 'settings', 'match' => 'account.settings'],
        ];
@endphp

<nav aria-label="Account" class="mb-8 flex gap-2 overflow-x-auto border-b border-cg-border-soft pb-0">
    @foreach ($links as $link)
        <a
            href="{{ route($link['route']) }}"
            class="flex shrink-0 items-center gap-2 rounded-t-lg border-b-2 px-4 py-3 text-sm font-semibold transition
                {{ request()->routeIs($link['match'])
                    ? 'border-cg-red text-cg-white'
                    : 'border-transparent text-cg-muted hover:text-cg-white' }}"
        >
            <x-icon :name="$link['icon']" :size="15" />
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>
