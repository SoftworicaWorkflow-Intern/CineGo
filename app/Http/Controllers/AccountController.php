<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\City;
use App\Models\Movie;
use App\Services\TicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AccountController extends Controller
{
    /**
     * /account/dashboard — overview, upcoming tickets and recent activity.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $bookings = Booking::query()->forUser($user->id)->with(['seats', 'cinema.city'])->get();

        $upcoming = $bookings->filter(fn (Booking $booking) => $booking->is_upcoming())->values();
        $recent = $bookings->take(5)->values();

        $stats = [
            'bookings' => $bookings->count(),
            'upcoming' => $upcoming->count(),
            'spent' => $bookings
                ->where('status', Booking::STATUS_CONFIRMED)
                ->sum('total_amount'),
            'favorites' => $user->favoriteMovies()->count(),
        ];

        $favorites = $user->favoriteMovies()->latest('favorites.created_at')->limit(6)->get();

        return view('account.dashboard', compact('user', 'upcoming', 'recent', 'stats', 'favorites'));
    }

    /**
     * /account/bookings — upcoming tickets and full history.
     */
    public function bookings(Request $request): View
    {
        $tab = $request->query('tab', 'upcoming');

        $query = Booking::query()->forUser($request->user()->id)->with(['seats', 'cinema.city', 'movie']);

        if ($tab === 'upcoming') {
            $query->where(function ($builder) {
                $builder->where('show_date', '>', now()->toDateString())
                    ->orWhere(function ($today) {
                        $today->where('show_date', now()->toDateString())
                            ->where('start_time', '>=', now()->format('H:i:s'));
                    });
            })->where('status', '!=', Booking::STATUS_CANCELLED);
        } elseif ($tab === 'history') {
            $query->where(function ($builder) {
                $builder->where('show_date', '<', now()->toDateString())
                    ->orWhere('status', Booking::STATUS_CANCELLED);
            });
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

        return view('account.bookings', compact('bookings', 'tab'));
    }

    /**
     * /account/bookings/{booking} — single booking with its digital ticket.
     */
    public function booking(Request $request, Booking $booking): View
    {
        $this->authorizeBooking($request, $booking);

        $booking->load(['seats', 'movie', 'cinema.city', 'hall']);

        return view('booking.ticket', [
            'booking' => $booking,
            'qrSvg' => app(TicketService::class)->qrSvg($booking),
        ]);
    }

    /**
     * /account/profile — personal details.
     */
    public function profile(Request $request): View
    {
        return view('account.profile', [
            'user' => $request->user(),
            'cities' => City::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Save profile details.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:160|unique:users,email,'.$request->user()->id,
            'phone' => 'nullable|string|max:20',
            'city_id' => 'nullable|integer|exists:cities,id',
        ], [
            'name.required' => ui('validation_name', 'Please enter your name.'),
            'email.required' => ui('validation_email', 'Please enter your email address.'),
            'email.unique' => ui('email_taken', 'An account with this email already exists.'),
        ]);

        $request->user()->update($validated);

        return back()->with('success', ui('profile_updated', 'Profile updated.'));
    }

    /**
     * /account/settings — password, language and preferences.
     */
    public function settings(Request $request): View
    {
        return view('account.settings', ['user' => $request->user()]);
    }

    /**
     * Change the password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'current_password.required' => ui('validation_current_password', 'Please enter your current password.'),
            'current_password.current_password' => ui('current_password_wrong', 'That is not your current password.'),
            'password.confirmed' => ui('password_mismatch', 'The passwords do not match.'),
            'password.min' => ui('password_short', 'Password must be at least 8 characters.'),
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', ui('password_updated', 'Password updated.'));
    }

    /**
     * Save the language + city preference to the account.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_language' => 'required|in:en,np',
            'city_id' => 'nullable|integer|exists:cities,id',
        ]);

        $request->user()->update($validated);
        session(['locale' => $validated['preferred_language']]);

        if (! empty($validated['city_id'])) {
            session(['city_id' => (int) $validated['city_id']]);
        }

        return back()->with('success', ui('preferences_saved', 'Preferences saved.'));
    }

    /**
     * /account/favourite-movies — the customer's saved list.
     */
    public function favorites(Request $request): View
    {
        $movies = $request->user()->favoriteMovies()->latest('favorites.created_at')->paginate(12);

        return view('account.favorites', compact('movies'));
    }

    /**
     * Toggle a movie in the customer's favourites (AJAX or form post).
     */
    public function toggleFavorite(Request $request, Movie $movie): RedirectResponse
    {
        $request->user()->favoriteMovies()->toggle($movie);

        return back()->with('success', ui('favorites_updated', 'Your favourites were updated.'));
    }

    /**
     * /account/notifications — in-app notifications.
     */
    public function notifications(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(15);

        return view('account.notifications', compact('notifications'));
    }

    /**
     * Mark every notification as read.
     */
    public function markNotificationsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', ui('notifications_read', 'Notifications marked as read.'));
    }

    private function authorizeBooking(Request $request, Booking $booking): void
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
    }
}
