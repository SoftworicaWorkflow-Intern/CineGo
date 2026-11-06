@extends('layouts.admin')

@section('title', ui('showtimes_for', 'Showtimes'))

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('admin.showtimes.create') }}" class="btn-primary">
            <x-icon name="plus" :size="17" /> {{ ui('add_showtime', 'Add showtime') }}
        </a>
    </div>

    <div class="card overflow-x-auto overflow-y-hidden">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-cg-border-soft text-left text-xs uppercase tracking-wide text-cg-dim">
                    <th class="px-5 py-4 font-bold text-black">{{ ui('date', 'Date') }}</th>
                    <th class="px-5 py-4 font-bold text-black">{{ ui('times', 'Time') }}</th>
                    <th class="px-5 py-4 font-bold text-black">{{ ui('movies', 'Movie') }}</th>
                    <th class="px-5 py-4 font-bold text-black">{{ ui('screens', 'Screen') }}</th>
                    <th class="px-5 py-4 font-bold text-black">{{ ui('format', 'Format') }}</th>
                    <th class="px-5 py-4 font-bold text-black">{{ ui('prices', 'Prices') }}</th>
                    <th class="px-5 py-4 text-right font-bold text-black">{{ ui('actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cg-border-soft">
                @forelse ($showtimes as $showtime)
                    <tr class="transition hover:bg-cg-charcoal">
                        <td class="px-5 py-4">
                            <span class="font-medium text-cg-white">{{ $showtime->show_date->format('D, M j') }}</span>
                            @unless ($showtime->is_active)
                                <span class="mt-1 block text-[11px] text-cg-dim">{{ ui('inactive', 'Inactive') }}</span>
                            @endunless
                        </td>
                        <td class="px-5 py-4 font-mono text-cg-white">{{ $showtime->start_time_label() }}</td>
                        <td class="px-5 py-4 text-cg-muted">{{ $showtime->movie?->localised_title() }}</td>
                        <td class="px-5 py-4 text-cg-muted">{{ $showtime->hall?->name }}</td>
                        <td class="px-5 py-4"><span class="badge-dark">{{ $showtime->format }}</span></td>
                        <td class="px-5 py-4 text-xs text-cg-dim">
                            N {{ number_format($showtime->price_normal) }} ·
                            P {{ number_format($showtime->price_premium) }} ·
                            R {{ number_format($showtime->price_recliner) }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.showtimes.edit', $showtime) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    <x-icon name="edit" :size="14" /> {{ ui('edit', 'Edit') }}
                                </a>
                                <form action="{{ route('admin.showtimes.destroy', $showtime) }}" method="POST"
                                    data-confirm="{{ ui('delete_confirm_showtime', 'Delete this showtime? Existing bookings keep their snapshots.') }}">
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
                        <td colspan="7" class="px-5 py-10 text-center text-cg-muted">
                            {{ ui('no_showtimes', 'No showtimes scheduled.') }}
                            <a href="{{ route('admin.showtimes.create') }}" class="font-semibold text-cg-red hover:underline">
                                {{ ui('add_showtime', 'Add showtime') }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $showtimes->links() }}
    </div>
@endsection
