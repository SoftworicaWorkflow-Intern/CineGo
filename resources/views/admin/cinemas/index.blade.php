@extends('layouts.admin')

@section('title', ui('cinemas', 'Cinemas'))

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <form action="{{ route('admin.cinemas.index') }}" method="GET" class="flex gap-2">
            <div class="relative">
                <x-icon name="search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-cg-red" />
                <input type="search" name="q" value="{{ $term }}" placeholder="{{ ui('search', 'Search') }}…" class="input h-11 min-h-11 pl-9 sm:w-64">
            </div>
            <button type="submit" class="btn-outline px-4">{{ ui('search', 'Search') }}</button>
        </form>

        <a href="{{ route('admin.cinemas.create') }}" class="btn-primary">
            <x-icon name="plus" :size="17" /> {{ ui('add_cinema', 'Add cinema') }}
        </a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-cg-border-soft text-left text-xs uppercase tracking-wide text-cg-dim">
                    <th class="px-5 py-4 font-semibold">{{ ui('cinemas', 'Cinema') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('select_city', 'City') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('screens', 'Screens') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('status', 'Status') }}</th>
                    <th class="px-5 py-4 text-right font-semibold">{{ ui('actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cg-border-soft">
                @forelse ($cinemas as $cinema)
                    <tr class="transition hover:bg-cg-charcoal">
                        <td class="px-5 py-4">
                            <a href="{{ route('cinemas.show', $cinema) }}" class="block font-medium text-cg-white hover:text-cg-red">
                                {{ $cinema->localised_name() }}
                            </a>
                            <span class="block max-w-md truncate text-xs text-cg-dim">{{ $cinema->address }}</span>
                        </td>
                        <td class="px-5 py-4 text-cg-muted">{{ $cinema->city?->display_name() }}</td>
                        <td class="px-5 py-4 text-cg-muted">{{ $cinema->halls_count ?? $cinema->halls()->count() }}</td>
                        <td class="px-5 py-4">
                            @if ($cinema->is_active)
                                <span class="badge-green">{{ ui('active', 'Active') }}</span>
                            @else
                                <span class="badge-dark">{{ ui('inactive', 'Inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.cinemas.edit', $cinema) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    <x-icon name="edit" :size="14" /> {{ ui('edit', 'Edit') }}
                                </a>
                                <form action="{{ route('admin.cinemas.destroy', $cinema) }}" method="POST"
                                    data-confirm="{{ ui('delete_confirm_cinema', 'Delete this cinema and all its screens? This cannot be undone.') }}">
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
                        <td colspan="5" class="px-5 py-10 text-center text-cg-muted">{{ ui('no_results', 'Nothing matched your search.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $cinemas->links() }}
    </div>
@endsection
