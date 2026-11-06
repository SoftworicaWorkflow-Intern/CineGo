<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\MovieController as AdminMovieController;
use App\Http\Controllers\Admin\OfferController as AdminOfferController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\ShowtimeController as AdminShowtimeController;
use App\Http\Controllers\Admin\UpcomingController as AdminUpcomingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PreferenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Preferences (language & city)
|--------------------------------------------------------------------------
*/

Route::post('/language', [PreferenceController::class, 'setLanguage'])->name('locale.set');
Route::post('/city', [PreferenceController::class, 'setCity'])->name('city.set');

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');
Route::post('/movies/{movie:slug}/reviews', [MovieController::class, 'storeReview'])
    ->middleware('auth')->name('movies.review');
Route::post('/movies/{movie:slug}/favorite', [AccountController::class, 'toggleFavorite'])
    ->middleware('auth')->name('movies.favorite');

Route::redirect('/cinemas', '/movies');
Route::get('/cinemas/{path}', fn () => to_route('movies.index'))->where('path', '.*');

Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
Route::post('/offers/validate', [OfferController::class, 'validate'])->name('offers.validate');

Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'storeContact'])->name('pages.contact.store');
Route::post('/newsletter-subscriptions', NewsletterSubscriptionController::class)
    ->middleware('throttle:5,1')
    ->name('newsletter-subscriptions.store');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/refund-policy', [PageController::class, 'refund'])->name('pages.refund');

/*
|--------------------------------------------------------------------------
| Booking flow
|--------------------------------------------------------------------------
*/

Route::get('/book/{showtime}', [BookingController::class, 'seats'])->name('booking.seats');
Route::post('/book/{showtime}', [BookingController::class, 'selectSeats'])->name('booking.select');
Route::get('/book/{showtime}/checkout', [BookingController::class, 'checkout'])->name('booking.checkout');
Route::post('/book/{showtime}/checkout', [BookingController::class, 'placeOrder'])->name('booking.place');

Route::get('/ticket/{bookingCode}', [BookingController::class, 'ticket'])->name('booking.ticket');
Route::get('/ticket/{bookingCode}/download', [BookingController::class, 'download'])->name('booking.download');
Route::get('/verify/{bookingCode}', [BookingController::class, 'verify'])->name('booking.verify');
Route::post('/verify/{bookingCode}', [BookingController::class, 'checkIn'])
    ->middleware('auth')->name('booking.checkin');

Route::get('/payments/{payment}', [PaymentController::class, 'process'])->name('payment.process');
Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payment.verify');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Customer account
|--------------------------------------------------------------------------
*/

Route::prefix('account')->name('account.')->middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('account.dashboard'));
    Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/bookings', [AccountController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/{booking}', [AccountController::class, 'booking'])->name('booking');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::post('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/settings', [AccountController::class, 'settings'])->name('settings');
    Route::post('/settings/password', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::post('/settings/preferences', [AccountController::class, 'updatePreferences'])->name('preferences.update');
    Route::get('/favorites', [AccountController::class, 'favorites'])->name('favorites');
    Route::get('/notifications', [AccountController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/read', [AccountController::class, 'markNotificationsRead'])->name('notifications.read');
    Route::get('/payments', [PaymentController::class, 'history'])->name('payments');
});

/*
|--------------------------------------------------------------------------
| Admin panel (RBAC: is_admin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/upcoming', [AdminUpcomingController::class, 'index'])->name('upcoming.index');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');

    Route::resource('movies', AdminMovieController::class)->except(['show']);
    Route::resource('showtimes', AdminShowtimeController::class)->except(['show']);
    Route::resource('offers', AdminOfferController::class)->except(['show']);
    Route::resource('announcements', AdminAnnouncementController::class)->except(['create', 'edit', 'show']);

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');
    Route::post('/bookings/{booking}/check-in', [AdminBookingController::class, 'checkIn'])->name('bookings.checkin');

    Route::get('/messages', [AdminMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [AdminMessageController::class, 'show'])->name('messages.show');
    Route::delete('/messages/{message}', [AdminMessageController::class, 'destroy'])->name('messages.destroy');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
});
