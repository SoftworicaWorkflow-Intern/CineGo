@props(['kicker' => null, 'title', 'subtitle' => null, 'href' => null, 'label' => null])

<div {{ $attributes->merge(['class' => 'mb-7 flex flex-wrap items-end justify-between gap-4']) }}>
    <div>
        @if ($kicker)
            <p class="section-kicker">{{ $kicker }}</p>
        @endif
        <h2 class="section-title">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-2 max-w-2xl text-sm text-cg-muted sm:text-base">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($href && $label)
        <a href="{{ $href }}" class="btn-outline shrink-0 px-4 py-2 text-sm">
            {{ $label }} <x-icon name="arrow-right" :size="15" />
        </a>
    @endif
</div>
