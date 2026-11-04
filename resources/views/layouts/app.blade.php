<!DOCTYPE html>
<html lang="{{ ($currentLocale ?? 'en') === 'np' ? 'ne' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title') @yield('title') · CineGo @else CineGo · Discover Movies. Book Your Seats. @endif</title>
    <meta name="description" content="@yield('meta', 'CineGo — book movie tickets online across Nepal. Pick your cinema, choose your seats and get an instant QR ticket.')">
    <meta name="theme-color" content="#ffffff">

    <link rel="icon" href="{{ asset('img/icons/icon.svg') }}" type="image/svg+xml">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CineGo">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    @include('partials.header')

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.flash')
    @include('partials.trailer-modal')

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function () {});
            });
        }
    </script>
</body>
</html>
