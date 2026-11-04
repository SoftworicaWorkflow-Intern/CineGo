@extends('layouts.app')

@section('title', ui('notifications', 'Notifications'))

@section('content')
    <div class="container-page py-10">
        <x-breadcrumbs :items="[['label' => ui('my_account', 'My Account'), 'href' => route('account.dashboard')], ['label' => ui('notifications', 'Notifications')]]" />

        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-kicker">{{ ui('my_account', 'My Account') }}</p>
                <h1 class="section-title">{{ ui('notifications', 'Notifications') }}</h1>
            </div>

            @if ($notifications->where('read_at', null)->isNotEmpty())
                <form action="{{ route('account.notifications.read') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-outline px-4 py-2 text-sm">
                        <x-icon name="check" :size="15" /> {{ ui('mark_all_read', 'Mark all as read') }}
                    </button>
                </form>
            @endif
        </div>

        @include('partials.account-nav')

        @if ($notifications->isEmpty())
            <x-empty
                :title="ui('no_notifications', 'Nothing here yet.')"
                :text="ui('no_notifications_text', 'Booking confirmations and updates will appear here.')"
                icon="bell"
            />
        @else
            <ul class="space-y-3">
                @foreach ($notifications as $notification)
                    @php($data = $notification->data)
                    <li class="card flex items-start gap-4 p-4 sm:p-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg
                            {{ $notification->read_at ? 'bg-cg-charcoal text-cg-dim' : 'bg-cg-red/15 text-cg-red' }}">
                            <x-icon name="{{ str_contains($notification->type, 'BookingConfirmed') ? 'ticket' : 'bell' }}" :size="18" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="font-semibold text-cg-white">
                                    {{ ui('booking_confirmed', 'Booking confirmed!') }}
                                    @unless ($notification->read_at)
                                        <span class="ml-1.5 inline-block h-2 w-2 rounded-full bg-cg-red align-middle"></span>
                                    @endunless
                                </p>
                                <span class="text-xs text-cg-dim">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>

                            <p class="mt-1 text-sm text-cg-muted">
                                {{ $data['movie'] ?? '' }} · {{ $data['cinema'] ?? '' }} ·
                                {{ $data['show_date'] ?? '' }} {{ $data['start_time'] ?? '' }} ·
                                {{ ui('seats', 'Seats') }}: {{ $data['seats'] ?? '' }}
                            </p>

                            @if (! empty($data['url']))
                                <a href="{{ $data['url'] }}" class="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-cg-red hover:underline">
                                    <x-icon name="qr-code" :size="14" /> {{ ui('view_ticket', 'View ticket') }}
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
