@props(['current' => 1, 'showtime' => null])

@php
    $steps = [
        ['label' => ui('step_select_cinema', 'Select Cinema'), 'icon' => 'armchair'],
        ['label' => ui('step_select_date', 'Select Date'), 'icon' => 'calendar'],
        ['label' => ui('step_select_showtime', 'Showtime'), 'icon' => 'clock'],
        ['label' => ui('step_select_seats', 'Select Seats'), 'icon' => 'armchair'],
        ['label' => ui('step_customer_info', 'Your Details'), 'icon' => 'user'],
        ['label' => ui('step_payment', 'Payment'), 'icon' => 'credit-card'],
        ['label' => ui('step_confirmation', 'Confirmation'), 'icon' => 'check'],
    ];

    // Steps 1–3 always point back at the showtime picker unless a showtime
    // context exists (the seat/checkout pages pass $showtime).
    $backHref = ($showtime && $showtime->movie)
        ? route('movies.show', $showtime->movie) . '#showtimes'
        : route('movies.index');
@endphp

<ol class="mb-7 flex items-start gap-1 overflow-x-auto pb-2 sm:gap-0" aria-label="Booking progress">
    @foreach ($steps as $index => $step)
        @php($number = $index + 1)
        @php($state = $number < $current ? 'done' : ($number === $current ? 'active' : 'todo'))

        <li class="flex min-w-16 flex-1 flex-col items-center gap-1.5 sm:min-w-0" @if ($number === $current) aria-current="step" @endif>
            <div class="flex w-full items-center">
                @if ($index > 0)
                    <span class="h-px flex-1 {{ $number <= $current ? 'bg-cg-red' : 'bg-cg-border' }}"></span>
                @endif

                @if ($state === 'done' && $number <= 3)
                    <a href="{{ $backHref }}" title="{{ $step['label'] }}" class="cg-step-dot cg-step-done">
                        <x-icon name="check" :size="14" />
                    </a>
                @else
                    <span class="cg-step-dot {{ $state === 'done' ? 'cg-step-done' : ($state === 'active' ? 'cg-step-active-dot' : 'cg-step-todo') }}">
                        {{ $number }}
                    </span>
                @endif

                @if ($index < count($steps) - 1)
                    <span class="h-px flex-1 {{ $number < $current ? 'bg-cg-red' : 'bg-cg-border' }}"></span>
                @endif
            </div>

            <span class="text-center text-[10px] font-medium leading-tight sm:text-[11px]
                {{ $state === 'active' ? 'text-cg-white' : ($state === 'done' ? 'text-cg-muted' : 'text-cg-dim') }}">
                {{ $step['label'] }}
            </span>
        </li>
    @endforeach
</ol>
