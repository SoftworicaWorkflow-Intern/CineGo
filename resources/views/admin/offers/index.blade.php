@extends('layouts.admin')

@section('title', ui('offers', 'Offers'))

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('admin.offers.create') }}" class="btn-primary">
            <x-icon name="plus" :size="17" /> {{ ui('add_offer', 'Add offer') }}
        </a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full min-w-[820px] text-sm">
            <thead>
                <tr class="border-b border-cg-border-soft text-left text-xs uppercase tracking-wide text-cg-dim">
                    <th class="px-5 py-4 font-semibold">{{ ui('offer', 'Offer') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('promo_code', 'Code') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('discount', 'Discount') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('validity', 'Validity') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('status', 'Status') }}</th>
                    <th class="px-5 py-4 text-right font-semibold">{{ ui('actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cg-border-soft">
                @forelse ($offers as $offer)
                    <tr class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4">
                            <span class="block font-medium text-cg-white">{{ $offer->title }}</span>
                            <span class="block max-w-sm truncate text-xs text-cg-dim">{{ $offer->description }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @if ($offer->code)
                                <code class="rounded bg-cg-red/15 px-2 py-1 font-mono text-xs font-bold text-cg-red">{{ $offer->code }}</code>
                            @else
                                <span class="text-cg-dim">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-semibold text-cg-white">
                            @if ($offer->discount_type === 'percentage')
                                {{ rtrim(rtrim(number_format($offer->discount_value, 2), '0'), '.') }}%
                            @else
                                Rs. {{ number_format($offer->discount_value) }}
                            @endif
                            @if ($offer->min_amount)
                                <span class="block text-[11px] font-normal text-cg-dim">
                                    {{ ui('min_spend', 'Min.') }} Rs. {{ number_format($offer->min_amount) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs text-cg-muted">
                            @if ($offer->valid_from || $offer->valid_to)
                                {{ $offer->valid_from?->format('M j, Y') ?? '—' }} → {{ $offer->valid_to?->format('M j, Y') ?? '∞' }}
                            @else
                                <span class="text-cg-dim">{{ ui('always', 'Always') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($offer->is_active)
                                <span class="badge-green">{{ ui('active', 'Active') }}</span>
                            @else
                                <span class="badge-dark">{{ ui('inactive', 'Inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.offers.edit', $offer) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    <x-icon name="edit" :size="14" /> {{ ui('edit', 'Edit') }}
                                </a>
                                <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST"
                                    data-confirm="{{ ui('delete_confirm_offer', 'Delete this offer?') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-ghost px-3 py-1.5 text-xs text-cg-red">
                                        <x-icon name="trash" :size="14" /> {{ ui('delete', 'Delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-cg-muted">{{ ui('no_offers', 'No offers yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $offers->links() }}
    </div>
@endsection
