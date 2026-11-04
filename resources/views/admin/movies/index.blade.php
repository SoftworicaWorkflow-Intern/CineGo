@extends('layouts.admin')

@section('title', ui('movies', 'Movies'))

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <form action="{{ route('admin.movies.index') }}" method="GET" class="flex gap-2">
            <div class="relative">
                <x-icon name="search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-cg-red" />
                <input type="search" name="q" value="{{ $term }}" placeholder="{{ ui('search', 'Search') }}…" class="input h-11 min-h-11 pl-9 sm:w-64">
            </div>
            <button type="submit" class="btn-outline px-4">{{ ui('search', 'Search') }}</button>
        </form>

        <a href="{{ route('admin.movies.create') }}" class="btn-primary">
            <x-icon name="plus" :size="17" /> {{ ui('add_movie', 'Add movie') }}
        </a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-cg-border-soft text-left text-xs uppercase tracking-wide text-cg-dim">
                    <th class="px-5 py-4 font-semibold">{{ ui('movies', 'Movie') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('release_date', 'Release') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('language_label', 'Language') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('status', 'Status') }}</th>
                    <th class="px-5 py-4 text-right font-semibold">{{ ui('actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cg-border-soft">
                @forelse ($movies as $movie)
                    <tr class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $movie->poster_url }}" alt="" class="h-14 w-10 rounded object-cover" loading="lazy">
                                <div class="min-w-0">
                                    <a href="{{ route('movies.show', $movie) }}" class="block truncate font-medium text-cg-white hover:text-cg-red">
                                        {{ $movie->localised_title() }}
                                    </a>
                                    <span class="block truncate text-xs text-cg-dim">{{ $movie->genre_label() }} · {{ $movie->duration_label() }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-cg-muted">{{ $movie->release_date?->format('M j, Y') }}</td>
                        <td class="px-5 py-4 text-cg-muted">{{ $movie->language }}</td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @if ($movie->is_now_showing)<span class="badge-green">{{ ui('now_showing', 'Now Showing') }}</span>@endif
                                @if ($movie->is_coming_soon)<span class="badge-amber">{{ ui('coming_soon', 'Coming Soon') }}</span>@endif
                                @if ($movie->is_featured)<span class="badge-red">{{ ui('featured', 'Featured') }}</span>@endif
                                @unless ($movie->is_active)<span class="badge-dark">{{ ui('inactive', 'Inactive') }}</span>@endunless
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.movies.edit', $movie) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    <x-icon name="edit" :size="14" /> {{ ui('edit', 'Edit') }}
                                </a>
                                <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST"
                                    data-confirm="{{ ui('delete_confirm', 'Delete this movie? This cannot be undone.') }}">
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
        {{ $movies->links() }}
    </div>
@endsection
