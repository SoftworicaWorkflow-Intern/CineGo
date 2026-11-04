@props(['title', 'text' => null, 'icon' => 'info', 'actionLabel' => null, 'actionHref' => null])

<div {{ $attributes->merge(['class' => 'card flex flex-col items-center justify-center gap-3 px-6 py-14 text-center']) }}>
    <span class="flex h-14 w-14 items-center justify-center rounded-full border border-cg-border bg-cg-charcoal text-cg-red">
        <x-icon :name="$icon" :size="26" />
    </span>
    <h3 class="text-lg font-semibold text-cg-white">{{ $title }}</h3>
    @if ($text)
        <p class="max-w-md text-sm text-cg-muted">{{ $text }}</p>
    @endif
    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}" class="btn-primary mt-2">{{ $actionLabel }}</a>
    @endif
</div>
