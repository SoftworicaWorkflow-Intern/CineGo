@props(['size' => 36])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <img
        src="{{ asset('img/cinego-logo.png') }}?v=2"
        alt="CineGo"
        width="{{ (int) round($size * 930 / 185) }}"
        height="{{ $size }}"
        style="width: {{ (int) round($size * 930 / 185) }}px; height: {{ $size }}px;"
        class="block shrink-0"
    >
</span>
