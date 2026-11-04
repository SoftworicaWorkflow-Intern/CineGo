@php
    $code = $code ?? 404;

    $titles = [
        403 => ui('error_403_title', 'Access denied'),
        419 => ui('error_419_title', 'Session expired'),
        500 => ui('error_500_title', 'Server error'),
    ];
    $texts = [
        403 => ui('error_403_text', 'You do not have permission to view this page.'),
        419 => ui('error_419_text', 'Your session expired. Please refresh and try again.'),
        500 => ui('error_500_text', 'Something went wrong on our side. Please try again.'),
    ];

    $statusTitle = $titles[$code] ?? ui('error_404_title', 'Page not found');
    $statusText = $texts[$code] ?? ui('error_404_text', 'The page you are looking for does not exist or has moved.');
@endphp

<div class="flex min-h-[60vh] flex-col items-center justify-center py-16 text-center">
    <p class="font-display text-8xl leading-none tracking-tight text-cg-red">{{ $code }}</p>

    <h1 class="mt-4 font-display text-3xl tracking-wide text-cg-white sm:text-4xl">{{ $statusTitle }}</h1>
    <p class="mt-3 max-w-md text-cg-muted">{{ $statusText }}</p>

    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="{{ route('home') }}" class="btn-primary">
            <x-icon name="home" :size="17" /> {{ ui('go_home', 'Go to homepage') }}
        </a>
        <a href="{{ route('movies.index') }}" class="btn-outline">
            <x-icon name="film" :size="17" /> {{ ui('explore_movies', 'Explore Movies') }}
        </a>
        @if ($code === 419)
            <button type="button" onclick="window.location.reload()" class="btn-ghost">
                <x-icon name="refresh" :size="16" /> {{ ui('refresh', 'Refresh') }}
            </button>
        @endif
    </div>
</div>
