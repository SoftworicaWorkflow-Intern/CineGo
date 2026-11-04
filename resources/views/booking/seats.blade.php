@extends('layouts.app')

@section('title', ui('step_select_seats', 'Select Seats').' · '.$showtime->movie->localised_title())

@php
    $booked = array_flip($bookedIds);
    $locked = array_flip($lockedIds);
    $selected = array_flip($selectedIds);
    $priceFor = fn (string $type) => (float) $showtime->price_for($type);
@endphp

@section('content')
    <div class="container-page py-8">
        {{-- Booking progress --}}
        @include('partials.steps', ['current' => 2])

        <div class="grid gap-8 lg:grid-cols-[1fr_320px]">
            <div class="min-w-0">
                {{-- Show summary --}}
                <div class="card mb-6 flex flex-wrap items-center gap-4 p-4 sm:p-5">
                    <img src="{{ $showtime->movie->poster_url }}" alt="" class="h-20 w-14 rounded-lg object-cover" width="56" height="80">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('movies.show', $showtime->movie) }}" class="font-semibold text-cg-white transition hover:text-cg-red">
                            {{ $showtime->movie->localised_title() }}
                        </a>
                        <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-cg-muted sm:text-sm">
                            <span class="inline-flex items-center gap-1.5"><x-icon name="armchair" :size="14" class="text-cg-red" />{{ $showtime->cinema->localised_name() }} · {{ $showtime->hall->name }}</span>
                            <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" :size="14" class="text-cg-red" />{{ $showtime->show_date->format('D, M j, Y') }}</span>
                            <span class="inline-flex items-center gap-1.5"><x-icon name="clock" :size="14" class="text-cg-red" />{{ $showtime->start_time_label() }}</span>
                            <span class="badge-dark">{{ $showtime->format }}</span>
                        </p>
                    </div>
                    <a href="{{ route('movies.show', $showtime->movie) }}#showtimes" class="btn-ghost px-3 py-2 text-xs">
                        {{ ui('change_show', 'Change show') }}
                    </a>
                </div>

                {{-- Seat map --}}
                <div class="card p-4 sm:p-7" data-no-scroll-reveal>
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <h1 class="font-display text-2xl tracking-wide text-cg-white">{{ ui('step_select_seats', 'Select Seats') }}</h1>
                        <span class="inline-flex items-center gap-2 text-xs text-cg-muted">
                            <span class="h-2 w-24 rounded-full cg-screen"></span>
                            {{ ui('screen_here', 'Screen this way') }}
                        </span>
                    </div>

                    @if ($errors->has('seats'))
                        <p class="mb-4 rounded-lg border border-cg-red/50 bg-cg-red/10 px-4 py-3 text-sm text-cg-white">
                            {{ $errors->first('seats') }}
                        </p>
                    @endif

                    <form action="{{ route('booking.select', $showtime) }}" method="POST" id="seat-form">
                        @csrf
                        <div id="seat-inputs"></div>

                        <div class="-mx-2 overflow-x-auto pb-2">
                            <div class="mx-auto flex min-w-max flex-col gap-2.5 px-2">
                                @foreach ($rows as $rowLabel => $seats)
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-5 text-center text-[11px] font-bold text-cg-dim">{{ $rowLabel }}</span>

                                        <div class="flex gap-2">
                                            @foreach ($seats as $seat)
                                                @php
                                                    $isBooked = isset($booked[$seat->id]);
                                                    $isLocked = isset($locked[$seat->id]);
                                                    $isSelected = isset($selected[$seat->id]);
                                                @endphp
                                                <button
                                                    type="button"
                                                    data-seat
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-type="{{ $seat->seat_type }}"
                                                    data-price="{{ $priceFor($seat->seat_type) }}"
                                                    data-label="{{ $seat->label() }}"
                                                    aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                                    aria-label="{{ $seat->label() }} — {{ $isBooked ? ui('seat_booked', 'Booked') : ($isLocked ? ui('seat_locked', 'Locked') : ($isSelected ? ui('seat_selected', 'Selected') : ui('seat_available', 'Available'))) }}"
                                                    {{ ($isBooked || $isLocked) ? 'disabled' : '' }}
                                                    class="cg-seat cg-seat-{{ $seat->seat_type }}
                                                        {{ $isBooked ? 'cg-seat-booked' : '' }}
                                                        {{ $isLocked ? 'cg-seat-locked' : '' }}
                                                        {{ $isSelected ? 'cg-seat-selected' : '' }}"
                                                >{{ $seat->seat_number }}</button>
                                            @endforeach
                                        </div>

                                        <span class="w-5 text-center text-[11px] font-bold text-cg-dim">{{ $rowLabel }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </form>

                    {{-- Legend --}}
                    <div class="mt-7 border-t border-cg-border-soft pt-5">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-cg-dim">{{ ui('seat_legend', 'Seat legend') }}</p>
                        <div class="flex flex-wrap gap-x-5 gap-y-2.5 text-xs text-cg-muted">
                            <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded border border-emerald-300 bg-emerald-100"></span>{{ ui('seat_available', 'Available') }}</span>
                            <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded border border-cg-red bg-cg-red"></span>{{ ui('seat_selected', 'Selected') }}</span>
                            <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded border border-blue-300 bg-blue-100"></span>{{ ui('seat_premium', 'Premium') }}</span>
                            <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded border border-purple-300 bg-purple-100"></span>{{ ui('seat_recliner', 'Recliner') }}</span>
                            <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded border border-amber-300 bg-amber-100"></span>{{ ui('seat_locked', 'Locked') }}</span>
                            <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded border border-gray-300 bg-gray-200"></span>{{ ui('seat_booked', 'Booked') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sticky booking summary --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="card p-5">
                    <h2 class="mb-4 font-semibold text-cg-white">{{ ui('booking_summary', 'Booking summary') }}</h2>

                    <dl class="space-y-2.5 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('movies', 'Movie') }}</dt>
                            <dd class="text-right font-medium text-cg-white">{{ $showtime->movie->localised_title() }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-cg-muted">{{ ui('dates', 'Date') }}</dt>
                            <dd class="text-right font-medium text-cg-white">{{ $showtime->show_date->format('M j, Y') }} · {{ $showtime->start_time_label() }}</dd>
                        </div>
                    </dl>

                    <div class="my-4 border-t border-cg-border-soft"></div>

                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-cg-dim">{{ ui('selected_seats', 'Selected seats') }}</p>
                    <div id="selected-seats" class="mb-3 flex min-h-8 flex-wrap gap-1.5">
                        <span class="text-sm text-cg-dim" id="no-seats-label">{{ ui('no_seats_selected', 'Select at least one seat to continue.') }}</span>
                    </div>

                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="text-cg-muted">{{ ui('subtotal', 'Subtotal') }}</span>
                        <span class="font-semibold text-cg-white">Rs. <span id="subtotal">0</span></span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-bold">
                        <span class="text-cg-white">{{ ui('total', 'Total') }}</span>
                        <span class="text-xl text-cg-red">Rs. <span id="total">0</span></span>
                    </div>

                    <p id="hold-timer" class="mt-3 hidden items-center justify-between rounded-lg border border-cg-border bg-cg-charcoal px-3 py-2 text-xs text-cg-amber">
                        <span class="inline-flex items-center gap-1.5"><x-icon name="clock" :size="13" /> {{ ui('expires_in', 'Seats held for') }}</span>
                        <span id="hold-time" class="font-mono font-bold">10:00</span>
                    </p>

                    <button type="submit" form="seat-form" id="proceed-button" class="btn-primary btn-lg btn-block mt-4" disabled>
                        {{ ui('proceed_to_payment', 'Proceed to payment') }}
                        <x-icon name="arrow-right" :size="17" />
                    </button>

                    <p class="mt-3 text-center text-xs text-cg-dim">
                        {{ ui('max_seats', 'You can book up to 10 seats per booking.') }}
                    </p>
                </div>
            </aside>
        </div>
    </div>

    <script>
        (function () {
            var MAX_SEATS = 10;
            var form = document.getElementById('seat-form');
            var inputContainer = document.getElementById('seat-inputs');
            var container = document.getElementById('selected-seats');
            var noSeatsLabel = document.getElementById('no-seats-label');
            var subtotalEl = document.getElementById('subtotal');
            var totalEl = document.getElementById('total');
            var proceedButton = document.getElementById('proceed-button');
            var holdTimer = document.getElementById('hold-timer');
            var holdTime = document.getElementById('hold-time');
            var countdown = null;
            var remaining = 600;

            var selected = new Map();

            function formatAmount(value) {
                return Number(value).toLocaleString('en-US');
            }

            function render() {
                container.innerHTML = '';

                if (selected.size === 0) {
                    container.appendChild(noSeatsLabel);
                    noSeatsLabel.classList.remove('hidden');
                    subtotalEl.textContent = '0';
                    totalEl.textContent = '0';
                    inputContainer.innerHTML = '';
                    proceedButton.disabled = true;
                    holdTimer.classList.add('hidden');
                    holdTimer.classList.remove('flex');
                    if (countdown) { window.clearInterval(countdown); countdown = null; }
                    return;
                }

                var subtotal = 0;
                var ids = [];

                selected.forEach(function (data, id) {
                    subtotal += data.price;
                    ids.push(id);

                    var chip = document.createElement('span');
                    chip.className = 'rounded-md border border-cg-red/50 bg-cg-red/15 px-2.5 py-1.5 text-xs font-bold text-cg-white';
                    chip.textContent = data.label;
                    container.appendChild(chip);
                });

                var fee = 0;
                subtotalEl.textContent = formatAmount(subtotal);
                totalEl.textContent = formatAmount(subtotal + fee);
                inputContainer.innerHTML = ids.map(function (id) {
                    return '<input type="hidden" name="seats[]" value="' + id + '">';
                }).join('');
                proceedButton.disabled = false;

                holdTimer.classList.remove('hidden');
                holdTimer.classList.add('flex');
                if (!countdown) startCountdown();
            }

            function startCountdown() {
                remaining = 600;
                countdown = window.setInterval(function () {
                    remaining -= 1;
                    if (remaining <= 0) {
                        window.clearInterval(countdown);
                        countdown = null;
                        window.location.reload();
                        return;
                    }
                    var minutes = Math.floor(remaining / 60);
                    var seconds = remaining % 60;
                    holdTime.textContent = minutes + ':' + String(seconds).padStart(2, '0');
                }, 1000);
            }

            document.querySelectorAll('[data-seat]').forEach(function (button) {
                if (button.disabled) return;

                button.addEventListener('click', function () {
                    var id = Number(button.getAttribute('data-seat-id'));
                    var label = button.getAttribute('data-label');
                    var price = Number(button.getAttribute('data-price'));

                    if (selected.has(id)) {
                        selected.delete(id);
                        button.classList.remove('cg-seat-selected');
                        button.setAttribute('aria-pressed', 'false');
                    } else {
                        if (selected.size >= MAX_SEATS) {
                            window.alert(@json(ui('max_seats', 'You can book up to 10 seats per booking.')));
                            return;
                        }
                        selected.set(id, { label: label, price: price });
                        button.classList.add('cg-seat-selected');
                        button.setAttribute('aria-pressed', 'true');
                    }

                    render();
                });
            });

            form.addEventListener('submit', function (event) {
                if (selected.size === 0) {
                    event.preventDefault();
                    window.alert(@json(ui('no_seats_selected', 'Select at least one seat to continue.')));
                    return;
                }
                proceedButton.disabled = true;
                proceedButton.classList.add('opacity-70');
            });

            render();
        })();
    </script>
@endsection
