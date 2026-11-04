<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    /**
     * Search and filter all bookings.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $term = $request->query('q');

        $bookings = Booking::query()
            ->with(['user', 'seats', 'cinema.city'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($term, function ($query) use ($term) {
                $query->where(function ($builder) use ($term) {
                    $builder->where('booking_code', 'like', '%'.$term.'%')
                        ->orWhere('customer_name', 'like', '%'.$term.'%')
                        ->orWhere('customer_phone', 'like', '%'.$term.'%')
                        ->orWhere('movie_title', 'like', '%'.$term.'%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => Booking::query()->count(),
            'confirmed' => Booking::query()->where('status', Booking::STATUS_CONFIRMED)->count(),
            'pending' => Booking::query()->where('status', Booking::STATUS_PENDING)->count(),
            'cancelled' => Booking::query()->where('status', Booking::STATUS_CANCELLED)->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'status', 'term', 'counts'));
    }

    /**
     * Booking detail used by staff.
     */
    public function show(Booking $booking): View
    {
        $booking->load(['user', 'seats', 'movie', 'cinema.city', 'hall', 'payments']);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Update booking status / payment state (staff action).
     */
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'mark_paid' => 'nullable|boolean',
        ]);

        if ($validated['status'] === Booking::STATUS_CANCELLED && $booking->status !== Booking::STATUS_CANCELLED) {
            // Cancelling frees the seats; record the reason in the audit trail.
            $booking->payments()->latest()->first()?->update([
                'status' => Payment::STATUS_FAILED,
                'provider_payload' => ['result' => 'CANCELLED_BY_STAFF', 'at' => now()->toIso8601String()],
            ]);
        }

        $booking->update([
            'status' => $validated['status'],
            'is_paid' => $request->boolean('mark_paid') || $booking->is_paid,
        ]);

        if ($validated['status'] === Booking::STATUS_CONFIRMED && $booking->is_paid && $booking->wasChanged(['status', 'is_paid'])) {
            $this->paymentService->notify($booking);
        }

        return back()->with('success', ui('admin_booking_updated', 'Booking updated.'));
    }

    /**
     * Check a ticket in from the staff view (idempotent).
     */
    public function checkIn(Booking $booking): RedirectResponse
    {
        if ($booking->checked_in_at === null) {
            $booking->update(['checked_in_at' => now()]);
        }

        return back()->with('success', ui('checked_in', 'Ticket marked as used.'));
    }
}
