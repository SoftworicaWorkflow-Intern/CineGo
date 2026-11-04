@extends('layouts.admin')

@section('title', ui('customers', 'Customers'))

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-end gap-3">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-2">
            <div class="relative">
                <x-icon name="search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-cg-red" />
                <input type="search" name="q" value="{{ $term }}" placeholder="{{ ui('search_user_hint', 'Name, email or phone…') }}" class="input h-11 min-h-11 pl-9 sm:w-64">
            </div>
            <button type="submit" class="btn-outline px-4">{{ ui('search', 'Search') }}</button>
        </form>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-cg-border-soft text-left text-xs uppercase tracking-wide text-cg-dim">
                    <th class="px-5 py-4 font-semibold">{{ ui('full_name', 'Customer') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('contact', 'Contact') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('city', 'City') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('bookings', 'Bookings') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('joined', 'Joined') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('role', 'Role') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cg-border-soft">
                @forelse ($users as $user)
                    <tr class="transition hover:bg-cg-charcoal">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-cg-red/15 text-sm font-bold text-cg-red">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                <span class="font-medium text-cg-white">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="block text-cg-muted">{{ $user->email }}</span>
                            <span class="block text-xs text-cg-dim">{{ $user->phone ?: '—' }}</span>
                        </td>
                        <td class="px-5 py-4 text-cg-muted">{{ $user->city?->display_name() ?? '—' }}</td>
                        <td class="px-5 py-4 font-semibold text-cg-white">{{ number_format($user->bookings_count) }}</td>
                        <td class="px-5 py-4 text-xs text-cg-muted">{{ $user->created_at->format('M j, Y') }}</td>
                        <td class="px-5 py-4">
                            @if ($user->is_admin)
                                <span class="badge-red">{{ ui('admin', 'Admin') }}</span>
                            @else
                                <span class="badge-dark">{{ ui('customer', 'Customer') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-cg-muted">{{ ui('no_results', 'Nothing matched your search.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endsection
