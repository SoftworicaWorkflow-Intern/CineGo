@extends('layouts.app')

@section('title', ui('payment_history', 'Payment history'))

@section('content')
    <div class="container-page py-10">
        <x-breadcrumbs :items="[['label' => ui('my_account', 'My Account'), 'href' => route('account.dashboard')], ['label' => ui('payment_history', 'Payment history')]]" />

        <div class="mb-8">
            <p class="section-kicker">{{ ui('my_account', 'My Account') }}</p>
            <h1 class="section-title">{{ ui('payment_history', 'Payment history') }}</h1>
        </div>

        @include('partials.account-nav')

        @if ($payments->isEmpty())
            <x-empty
                :title="ui('no_payments', 'No payments yet.')"
                :text="ui('no_payments_text', 'Your payment receipts will appear here after you book.')"
                icon="wallet"
                :action-label="ui('explore_movies', 'Explore Movies')"
                :action-href="route('movies.index')"
            />
        @else
            <div class="card overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-cg-border-soft text-left text-xs uppercase tracking-wide text-cg-dim">
                            <th class="px-5 py-4 font-semibold">{{ ui('booking_id', 'Reference') }}</th>
                            <th class="px-5 py-4 font-semibold">{{ ui('movies', 'Movie') }}</th>
                            <th class="px-5 py-4 font-semibold">{{ ui('payment_method', 'Method') }}</th>
                            <th class="px-5 py-4 font-semibold">{{ ui('date', 'Date') }}</th>
                            <th class="px-5 py-4 text-right font-semibold">{{ ui('total', 'Amount') }}</th>
                            <th class="px-5 py-4 text-right font-semibold">{{ ui('payment_status', 'Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cg-border-soft">
                        @foreach ($payments as $payment)
                            <tr class="transition hover:bg-white/[0.03]">
                                <td class="px-5 py-4 font-mono text-xs text-cg-muted">{{ $payment->reference }}</td>
                                <td class="px-5 py-4">
                                    @if ($payment->booking)
                                        <a href="{{ route('account.booking', $payment->booking) }}" class="font-medium text-cg-white hover:text-cg-red">
                                            {{ $payment->booking->movie_title }}
                                        </a>
                                    @else
                                        <span class="text-cg-dim">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-cg-muted">{{ $payment->provider_label() }}</td>
                                <td class="px-5 py-4 text-cg-muted">{{ $payment->created_at->format('M j, Y H:i') }}</td>
                                <td class="px-5 py-4 text-right font-semibold text-cg-white">
                                    Rs. {{ number_format($payment->amount) }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="{{ $payment->status === \App\Models\Payment::STATUS_SUCCESS ? 'badge-green' : ($payment->status === \App\Models\Payment::STATUS_FAILED ? 'badge-red' : 'badge-amber') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
@endsection
