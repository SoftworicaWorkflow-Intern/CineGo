@extends('layouts.admin')

@section('title', ui('messages', 'Messages'))

@php
    $tabs = [
        'unread' => ['label' => ui('unread', 'Unread'), 'count' => $unreadCount],
        'read' => ['label' => ui('read', 'Read'), 'count' => null],
        'all' => ['label' => ui('all', 'All'), 'count' => null],
    ];
@endphp

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-2">
            @foreach ($tabs as $key => $item)
                <a href="{{ route('admin.messages.index', ['tab' => $key]) }}"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition
                        {{ $tab === $key ? 'border-cg-red bg-cg-red text-white' : 'border-cg-border bg-cg-charcoal text-cg-muted hover:border-cg-red hover:text-white' }}">
                    {{ $item['label'] }}
                    @if ($item['count'] !== null)
                        <span class="ml-1 text-xs opacity-70">{{ number_format($item['count']) }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-cg-border-soft text-left text-xs uppercase tracking-wide text-cg-dim">
                    <th class="px-5 py-4 font-semibold">{{ ui('from', 'From') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('subject', 'Subject') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('message', 'Message') }}</th>
                    <th class="px-5 py-4 font-semibold">{{ ui('date', 'Date') }}</th>
                    <th class="px-5 py-4 text-right font-semibold">{{ ui('actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cg-border-soft">
                @forelse ($messages as $message)
                    <tr class="transition hover:bg-white/[0.03] {{ $message->is_read ? '' : 'bg-cg-red/[0.04]' }}">
                        <td class="px-5 py-4">
                            <span class="block font-medium text-cg-white">{{ $message->name }}</span>
                            <span class="block text-xs text-cg-dim">{{ $message->email }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.messages.show', $message) }}" class="text-cg-white hover:text-cg-red">
                                {{ $message->subject ?: '—' }}
                            </a>
                            @unless ($message->is_read)
                                <span class="ml-1.5 inline-block h-2 w-2 rounded-full bg-cg-red align-middle" title="{{ ui('unread', 'Unread') }}"></span>
                            @endunless
                        </td>
                        <td class="max-w-xs px-5 py-4">
                            <span class="block truncate text-cg-muted">{{ $message->message }}</span>
                        </td>
                        <td class="px-5 py-4 text-xs text-cg-dim">{{ $message->created_at->format('M j, Y H:i') }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.messages.show', $message) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    <x-icon name="mail" :size="14" /> {{ ui('open', 'Open') }}
                                </a>
                                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST"
                                    data-confirm="{{ ui('delete_confirm_message', 'Delete this message?') }}">
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
                        <td colspan="5" class="px-5 py-10 text-center text-cg-muted">{{ ui('no_messages', 'No messages here.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $messages->links() }}
    </div>
@endsection
