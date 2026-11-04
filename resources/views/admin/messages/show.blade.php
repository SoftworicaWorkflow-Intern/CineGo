@extends('layouts.admin')

@section('title', ui('message_details', 'Message'))

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.messages.index') }}" class="btn-ghost px-3 py-2 text-sm">
            <x-icon name="arrow-left" :size="15" /> {{ ui('back', 'Back') }}
        </a>

        <form action="{{ route('admin.messages.destroy', $message) }}" method="POST"
            data-confirm="{{ ui('delete_confirm_message', 'Delete this message?') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-ghost px-3 py-2 text-sm text-cg-red">
                <x-icon name="trash" :size="15" /> {{ ui('delete', 'Delete') }}
            </button>
        </form>
    </div>

    <div class="mx-auto max-w-3xl space-y-6">
        <section class="card p-5 sm:p-6">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3 border-b border-cg-border-soft pb-4">
                <div>
                    <p class="font-semibold text-cg-white">{{ $message->name }}</p>
                    <a href="mailto:{{ $message->email }}" class="text-sm text-cg-red hover:underline">{{ $message->email }}</a>
                    @if ($message->phone)
                        <p class="mt-0.5 text-sm text-cg-muted">{{ $message->phone }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm text-cg-muted">{{ $message->created_at->format('M j, Y H:i') }}</p>
                    @if ($message->is_read)
                        <span class="badge-green mt-1">{{ ui('read', 'Read') }}</span>
                    @else
                        <span class="badge-amber mt-1">{{ ui('unread', 'Unread') }}</span>
                    @endif
                </div>
            </div>

            @if ($message->subject)
                <p class="mb-3 font-display text-2xl tracking-wide text-cg-white">{{ $message->subject }}</p>
            @endif

            <p class="whitespace-pre-line text-sm leading-7 text-cg-muted">{{ $message->message }}</p>
        </section>

        <a href="mailto:{{ $message->email }}?subject=Re: {{ urlencode($message->subject ?: 'CineGo') }}" class="btn-primary">
            <x-icon name="mail" :size="16" /> {{ ui('reply_by_email', 'Reply by email') }}
        </a>
    </div>
@endsection
