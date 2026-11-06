<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Showtime;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Admin overview: KPIs, revenue chart and the latest bookings.
     */
    public function index(): View
    {
        $todayBookings = Booking::query()
            ->whereDate('created_at', today())
            ->count();

        $monthRevenue = (float) Payment::query()
            ->where('status', Payment::STATUS_SUCCESS)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        $stats = [
            'movies' => Movie::query()->active()->count(),
            'showtimes_today' => Showtime::query()->active()->whereDate('show_date', today())->count(),
            'bookings_today' => $todayBookings,
            'bookings_total' => Booking::query()->count(),
            'revenue_month' => $monthRevenue,
            'pending_messages' => ContactMessage::query()->where('is_read', false)->count(),
            'tickets_sold' => Booking::query()->where('status', Booking::STATUS_CONFIRMED)->sum('seat_count'),
        ];

        $recentBookings = Booking::query()
            ->latest()
            ->take(8)
            ->get();

        $chart = $this->bookingChart();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'chart'));
    }

    /**
     * Bookings and revenue for the last 14 days (consumed by Chart.js).
     *
     * @return array{labels: list<string>, bookings: list<int>, revenue: list<float>}
     */
    private function bookingChart(): array
    {
        $labels = [];
        $bookings = [];
        $revenue = [];

        for ($days = 13; $days >= 0; $days--) {
            $day = now()->subDays($days)->startOfDay();

            $labels[] = $day->format('M j');
            $bookings[] = Booking::query()->whereBetween('created_at', [$day, $day->copy()->endOfDay()])->count();
            $revenue[] = (float) Payment::query()
                ->where('status', Payment::STATUS_SUCCESS)
                ->whereBetween('created_at', [$day, $day->copy()->endOfDay()])
                ->sum('amount');
        }

        return [
            'labels' => $labels,
            'bookings' => $bookings,
            'revenue' => $revenue,
        ];
    }
}
