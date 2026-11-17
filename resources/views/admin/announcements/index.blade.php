@extends('layouts.admin')

@section('title', ui('announcements', 'Announcements'))

@section('content')
    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        {{-- Create --}}
        <section class="card h-fit p-5">
            <h2 class="mb-4 flex items-center gap-2 font-semibold text-cg-white">
                <x-icon name="plus" :size="17" class="text-cg-red" />
                {{ ui('new_announcement', 'New announcement') }}
            </h2>

            <form action="{{ route('admin.announcements.store') }}" method="POST" class="space-y-3.5">
                @csrf

                <div>
                    <label class="label" for="ann-message">{{ ui('message', 'Message') }} *</label>
                    <input id="ann-message" type="text" name="message" value="{{ old('message') }}" class="input" required maxlength="200"
                        placeholder="{{ ui('announcement_hint', 'e.g. 20% off on weekday morning shows') }}">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="ann-link">{{ ui('link', 'Link') }}</label>
                        <input id="ann-link" type="text" name="link" value="{{ old('link') }}" class="input" maxlength="300" placeholder="/offers">
                    </div>
                    <div>
                        <label class="label" for="ann-sort">{{ ui('sort_order', 'Sort order') }}</label>
                        <input id="ann-sort" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="input">
                    </div>
                </div>

                <label class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-cg-white">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded accent-cg-red" @checked(old('is_active', true))>
                    {{ ui('active', 'Active') }}
                </label>

                <button type="submit" class="btn-primary w-full justify-center">
                    <x-icon name="check" :size="16" /> {{ ui('publish', 'Publish') }}
                </button>
            </form>
        </section>

        {{-- List --}}
        <section class="space-y-4">
            @forelse ($announcements as $announcement)
                <div class="card p-5">
                    <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST" class="space-y-3.5">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="badge-dark font-mono">#{{ $announcement->sort_order }}</span>
                            @if ($announcement->is_active)
                                <span class="badge-green">{{ ui('active', 'Active') }}</span>
                            @else
                                <span class="badge-dark">{{ ui('inactive', 'Inactive') }}</span>
                            @endif
                        </div>

                        <div>
                            <label class="label" for="message-{{ $announcement->id }}">{{ ui('message', 'Message') }} *</label>
                            <input id="message-{{ $announcement->id }}" type="text" name="message" value="{{ old("message.$announcement->id", $announcement->message) }}"
                                class="input" required maxlength="200">
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="label" for="link-{{ $announcement->id }}">{{ ui('link', 'Link') }}</label>
                                <input id="link-{{ $announcement->id }}" type="text" name="link" value="{{ old("link.$announcement->id", $announcement->link) }}"
                                    class="input" maxlength="300" placeholder="/offers">
                            </div>
                            <div>
                                <label class="label" for="sort-{{ $announcement->id }}">{{ ui('sort_order', 'Sort order') }}</label>
                                <input id="sort-{{ $announcement->id }}" type="number" name="sort_order" value="{{ old("sort_order.$announcement->id", $announcement->sort_order) }}" class="input">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-cg-white">
                                <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded accent-cg-red"
                                    @checked(old("is_active.$announcement->id", $announcement->is_active))>
                                {{ ui('active', 'Active') }}
                            </label>

                            <div class="flex gap-2">
                                <button type="submit" class="btn-outline px-4 py-2 text-sm">
                                    <x-icon name="check" :size="15" /> {{ ui('save', 'Save') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="mt-3 border-t border-cg-border-soft pt-3 text-right"
                        data-confirm="{{ ui('delete_confirm_announcement', 'Delete this announcement?') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-cg-red hover:underline">
                            <x-icon name="trash" :size="13" class="inline" /> {{ ui('delete', 'Delete') }}
                        </button>
                    </form>
                </div>
            @empty
                <div class="card p-8 text-center text-cg-muted">
                    {{ ui('no_announcements', 'No announcements yet — add one to show in the ticker above the header.') }}
                </div>
            @endforelse
        </section>
    </div>
@endsection
