<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Showtime;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Admin overview: KPIs and revenue chart.
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

        $charts = $this->dashboardCharts();

        return view('admin.dashboard', compact('stats', 'charts'));
    }

    /**
     * @return array<string, array<string, list<int|float|string>>>
     */
    private function dashboardCharts(): array
    {
        return [
            'booking_trend' => $this->bookingTrendChart(),
            'booking_status' => $this->bookingStatusChart(),
            'payment_outcomes' => $this->paymentOutcomesChart(),
            'tickets_by_movie' => $this->ticketsByMovieChart(),
            'revenue_by_movie' => $this->revenueByMovieChart(),
            'bookings_by_cinema' => $this->bookingsByCinemaChart(),
            'bookings_by_weekday' => $this->bookingsByWeekdayChart(),
            'payment_methods' => $this->paymentMethodsChart(),
        ];
    }

    /**
     * Bookings and successful payment revenue for the last 14 days.
     *
     * @return array{labels: list<string>, bookings: list<int>, revenue: list<float>}
     */
    private function bookingTrendChart(): array
    {
        $today = now()->startOfDay();
        $start = $today->copy()->subDays(13);
        $end = $today->copy()->endOfDay();
        $period = [$start, $end];

        $bookingTotals = Booking::query()
            ->whereBetween('created_at', $period)
            ->selectRaw('DATE(created_at) as chart_date, COUNT(*) as total')
            ->groupBy('chart_date')
            ->pluck('total', 'chart_date');

        $revenueTotals = Payment::query()
            ->where('status', Payment::STATUS_SUCCESS)
            ->whereBetween('created_at', $period)
            ->selectRaw('DATE(created_at) as chart_date, SUM(amount) as total')
            ->groupBy('chart_date')
            ->pluck('total', 'chart_date');

        $labels = [];
        $bookings = [];
        $revenue = [];

        for ($days = 13; $days >= 0; $days--) {
            $day = $today->copy()->subDays($days);
            $date = $day->toDateString();

            $labels[] = $day->format('M j');
            $bookings[] = (int) $bookingTotals->get($date, 0);
            $revenue[] = (float) $revenueTotals->get($date, 0);
        }

        return [
            'labels' => $labels,
            'bookings' => $bookings,
            'revenue' => $revenue,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function bookingStatusChart(): array
    {
        $totals = Booking::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $statuses = [
            Booking::STATUS_CONFIRMED => 'Confirmed',
            Booking::STATUS_PENDING => 'Pending',
            Booking::STATUS_CANCELLED => 'Cancelled',
        ];

        return [
            'labels' => array_values($statuses),
            'values' => array_map(
                fn (string $status): int => (int) $totals->get($status, 0),
                array_keys($statuses),
            ),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function paymentOutcomesChart(): array
    {
        $totals = Payment::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $statuses = [
            Payment::STATUS_SUCCESS => 'Successful',
            Payment::STATUS_PENDING => 'Pending',
            Payment::STATUS_FAILED => 'Failed',
        ];

        return [
            'labels' => array_values($statuses),
            'values' => array_map(
                fn (string $status): int => (int) $totals->get($status, 0),
                array_keys($statuses),
            ),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function ticketsByMovieChart(): array
    {
        $movies = Booking::query()
            ->where('status', Booking::STATUS_CONFIRMED)
            ->selectRaw('movie_title as label, SUM(seat_count) as total')
            ->groupBy('movie_title')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'labels' => $movies->pluck('label')->all(),
            'values' => $movies->pluck('total')->map(fn (mixed $total): int => (int) $total)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function revenueByMovieChart(): array
    {
        $movies = Payment::query()
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->where('payments.status', Payment::STATUS_SUCCESS)
            ->selectRaw('bookings.movie_title as label, SUM(payments.amount) as total')
            ->groupBy('bookings.movie_title')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'labels' => $movies->pluck('label')->all(),
            'values' => $movies->pluck('total')->map(fn (mixed $total): float => (float) $total)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function bookingsByCinemaChart(): array
    {
        $cinemas = Booking::query()
            ->where('status', Booking::STATUS_CONFIRMED)
            ->selectRaw('cinema_name as label, COUNT(*) as total')
            ->groupBy('cinema_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'labels' => $cinemas->pluck('label')->all(),
            'values' => $cinemas->pluck('total')->map(fn (mixed $total): int => (int) $total)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function bookingsByWeekdayChart(): array
    {
        $end = now();
        $start = $end->copy()->subDays(6)->startOfDay();
        $dailyTotals = Booking::query()
            ->whereBetween('created_at', [$start, $end->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as chart_date, COUNT(*) as total')
            ->groupBy('chart_date')
            ->get();
        $values = array_fill(0, 7, 0);

        foreach ($dailyTotals as $dailyTotal) {
            $weekday = Carbon::parse((string) $dailyTotal->chart_date)->dayOfWeekIso - 1;
            $values[$weekday] += (int) $dailyTotal->total;
        }

        return [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'values' => $values,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function paymentMethodsChart(): array
    {
        $methods = Booking::query()
            ->where('status', Booking::STATUS_CONFIRMED)
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $methods->map(
                fn (Booking $booking): string => Booking::PAYMENT_METHODS[$booking->payment_method] ?? $booking->payment_method ?? 'Other',
            )->all(),
            'values' => $methods->pluck('total')->map(fn (mixed $total): int => (int) $total)->all(),
        ];
    }
}
