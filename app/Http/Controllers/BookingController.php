<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Offer;
use App\Models\Seat;
use App\Models\SeatLock;
use App\Models\Showtime;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\TicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private PaymentService $paymentService,
        private TicketService $ticketService,
    ) {}

    /**
     * Step: interactive seat map for a showtime.
     */
    public function seats(Request $request, Showtime $showtime): View
    {
        abort_unless($showtime->is_active && ! $showtime->is_past(), 404);

        $showtime->load(['movie', 'cinema.city', 'hall']);

        $statuses = $this->bookingService->seatStatuses($showtime, $this->holder());
        $seats = $showtime->hall->seats()->where('is_active', true)->orderBy('row_label')->orderBy('seat_number')->get();

        $rows = $seats->groupBy('row_label');
        $selectedIds = $this->draftSeatIds($showtime);

        $this->trimExpiredDrafts();

        return view('booking.seats', [
            'showtime' => $showtime,
            'rows' => $rows,
            'bookedIds' => $statuses['booked'],
            'lockedIds' => $statuses['locked'],
            'selectedIds' => $selectedIds,
        ]);
    }

    /**
     * Hold the chosen seats and move on to customer details.
     */
    public function selectSeats(Request $request, Showtime $showtime): RedirectResponse
    {
        abort_unless($showtime->is_active && ! $showtime->is_past(), 404);

        $validated = $request->validate([
            'seats' => 'required|array|min:1',
            'seats.*' => 'integer',
        ], [
            'seats.required' => ui('no_seats_selected', 'Select at least one seat to continue.'),
            'seats.min' => ui('no_seats_selected', 'Select at least one seat to continue.'),
        ]);

        $seatIds = array_map('intval', $validated['seats']);

        $conflicts = $this->bookingService->lockSeats($showtime, $seatIds, $this->holder());

        if ($conflicts !== []) {
            return back()
                ->withInput()
                ->withErrors(['seats' => ui('seats_taken', 'One of your seats was just booked by someone else. Please pick again.')]);
        }

        session([
            'booking_draft' => [
                'showtime_id' => $showtime->id,
                'seat_ids' => $seatIds,
                'expires_at' => now()->addMinutes(SeatLock::TTL_MINUTES)->toDateTimeString(),
            ],
        ]);

        return redirect()->route('booking.checkout', $showtime);
    }

    /**
     * Step: customer details, promo code and payment method.
     */
    public function checkout(Request $request, Showtime $showtime): View|RedirectResponse
    {
        $draft = $this->draft($showtime);

        if ($draft === null) {
            return redirect()->route('booking.seats', $showtime)
                ->withErrors(['seats' => ui('session_expired', 'Your seat hold has expired. Please select your seats again.')]);
        }

        $showtime->load(['movie', 'cinema.city', 'hall']);
        $seatIds = $draft['seat_ids'];

        $liveHolds = SeatLock::query()
            ->live()
            ->where('showtime_id', $showtime->id)
            ->whereIn('seat_id', $seatIds)
            ->where('holder', $this->holder())
            ->count();

        if ($liveHolds !== count($seatIds)) {
            return redirect()->route('booking.seats', $showtime)
                ->withErrors(['seats' => ui('session_expired', 'Your seat hold has expired. Please select your seats again.')]);
        }

        $totals = $this->bookingService->totals($showtime, $seatIds);
        $seats = Seat::whereIn('id', $seatIds)->orderBy('row_label')->orderBy('seat_number')->get();

        return view('booking.checkout', [
            'showtime' => $showtime,
            'seats' => $seats,
            'totals' => $totals,
            'paymentMethods' => Booking::PAYMENT_METHODS,
        ]);
    }

    /**
     * Create the booking and hand off to the chosen payment provider.
     */
    public function placeOrder(Request $request, Showtime $showtime): RedirectResponse
    {
        $draft = $this->draft($showtime);

        if ($draft === null) {
            return redirect()->route('booking.seats', $showtime)
                ->withErrors(['seats' => ui('session_expired', 'Your seat hold has expired. Please select your seats again.')]);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'nullable|email|max:160',
            'customer_phone' => 'required|string|min:7|max:20',
            'payment_method' => 'required|in:'.implode(',', array_keys(Booking::PAYMENT_METHODS)),
            'promo_code' => 'nullable|string|max:40',
        ], [
            'customer_name.required' => ui('validation_name', 'Please enter your name.'),
            'customer_phone.required' => ui('validation_phone', 'Please enter your mobile number.'),
            'customer_email.email' => ui('validation_email_invalid', 'Please enter a valid email address.'),
            'payment_method.required' => ui('validation_payment', 'Please choose a payment method.'),
        ]);

        $offer = null;
        if (! empty($validated['promo_code'])) {
            $offer = Offer::query()->currentlyValid()->where('code', trim($validated['promo_code']))->firstOrFail();
        }

        $booking = $this->bookingService->createBooking(
            showtime: $showtime,
            seatIds: $draft['seat_ids'],
            customer: $validated,
            offer: $offer,
            holder: $this->holder(),
            status: Booking::STATUS_PENDING,
        );

        session()->forget('booking_draft');

        // Remember guest bookings so the payment page can authorise them.
        if ($booking->user_id === null) {
            $guestIds = array_values(array_unique([...session('guest_booking_ids', []), $booking->id]));
            session(['guest_booking_ids' => $guestIds]);
        }

        if ($validated['payment_method'] === 'counter') {
            $this->paymentService->confirmCounterBooking($booking);

            return redirect()->route('booking.ticket', $booking->booking_code)
                ->with('success', ui('booking_confirmed', 'Booking confirmed!'));
        }

        $payment = $this->paymentService->initiate($booking, $validated['payment_method']);

        return redirect()->route('payment.process', $payment);
    }

    /**
     * The digital ticket, addressed by its booking code.
     */
    public function ticket(string $bookingCode): View
    {
        $booking = $this->findBooking($bookingCode);

        return view('booking.ticket', [
            'booking' => $booking,
            'qrSvg' => $this->ticketService->qrSvg($booking),
        ]);
    }

    /**
     * Download the ticket as a PDF.
     */
    public function download(string $bookingCode): Response
    {
        $booking = $this->findBooking($bookingCode);

        return $this->ticketService->pdf($booking)
            ->download('cinego-ticket-'.$booking->booking_code.'.pdf');
    }

    /**
     * Public gate verification page (scan the QR code at the entrance).
     */
    public function verify(string $bookingCode): View
    {
        $booking = $this->findBooking($bookingCode);

        return view('booking.verify', compact('booking'));
    }

    /**
     * Check a ticket in exactly once (idempotent).
     */
    public function checkIn(string $bookingCode): RedirectResponse
    {
        $booking = $this->findBooking($bookingCode);

        DB::transaction(function () use ($booking) {
            // Re-check inside the transaction so a double click cannot
            // check the same ticket in twice.
            $fresh = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if ($fresh->checked_in_at === null) {
                $fresh->update(['checked_in_at' => now()]);
            }
        });

        return back()->with('success', ui('checked_in', 'Ticket marked as used.'));
    }

    private function findBooking(string $bookingCode): Booking
    {
        return Booking::query()
            ->with(['seats', 'movie', 'cinema.city', 'hall'])
            ->where('booking_code', $bookingCode)
            ->firstOrFail();
    }

    private function holder(): string
    {
        return session()->getId();
    }

    /**
     * @return array{showtime_id: int, seat_ids: list<int>}|null
     */
    private function draft(Showtime $showtime): ?array
    {
        $draft = session('booking_draft');

        if (! is_array($draft) || (int) ($draft['showtime_id'] ?? 0) !== $showtime->id) {
            return null;
        }

        if (isset($draft['expires_at']) && now()->gte($draft['expires_at'])) {
            session()->forget('booking_draft');

            return null;
        }

        $seatIds = array_map('intval', $draft['seat_ids'] ?? []);

        if ($seatIds === []) {
            return null;
        }

        return ['showtime_id' => (int) $draft['showtime_id'], 'seat_ids' => $seatIds];
    }

    /**
     * @return list<int>
     */
    private function draftSeatIds(Showtime $showtime): array
    {
        return $this->draft($showtime)['seat_ids'] ?? [];
    }

    private function trimExpiredDrafts(): void
    {
        if ($draft = session('booking_draft')) {
            if (isset($draft['expires_at']) && now()->gte($draft['expires_at'])) {
                session()->forget('booking_draft');
            }
        }
    }
}
