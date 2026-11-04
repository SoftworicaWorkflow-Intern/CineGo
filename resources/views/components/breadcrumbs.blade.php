@props(['items' => []])

{{-- items: list of ['label' => string, 'href' => ?string] --}}
<nav aria-label="Breadcrumb" class="mb-6">
    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-cg-dim">
        <li>
            <a href="{{ route('home') }}" class="transition hover:text-cg-red">{{ ui('home', 'Home') }}</a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <x-icon name="chevron-right" :size="13" />
                @if (! empty($item['href']))
                    <a href="{{ $item['href'] }}" class="transition hover:text-cg-red">{{ $item['label'] }}</a>
                @else
                    <span class="text-cg-muted">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
