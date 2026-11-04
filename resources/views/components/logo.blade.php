@props(['size' => 36])

{{-- CineGo logo: a cinema ticket mark with a play triangle --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <rect x="3" y="9" width="42" height="30" rx="7" fill="#E50914" />
        <rect x="3" y="9" width="42" height="30" rx="7" fill="url(#cg-logo-gloss)" />
        <circle cx="3" cy="24" r="5.5" fill="#090909" />
        <circle cx="45" cy="24" r="5.5" fill="#090909" />
        <path d="M20 17.5v13l11-6.5z" fill="#FFFFFF" />
        <defs>
            <linearGradient id="cg-logo-gloss" x1="24" y1="9" x2="24" y2="39" gradientUnits="userSpaceOnUse">
                <stop stop-color="#FFFFFF" stop-opacity="0.22" />
                <stop offset="1" stop-color="#FFFFFF" stop-opacity="0" />
            </linearGradient>
        </defs>
    </svg>
    <span class="font-display text-2xl leading-none tracking-wider text-white sm:text-[26px]">
        Cine<span class="text-cg-red">Go</span>
    </span>
</span>
