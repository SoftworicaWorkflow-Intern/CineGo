<?php

use App\Models\Announcement;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Cinema;
use App\Models\City;
use App\Models\ContactMessage;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

/** Quick sanity check of the seeded demo data. */
$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';

$app = require $root.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$models = [
    'users' => User::class,
    'admins' => null,
    'cities' => City::class,
    'cinemas' => Cinema::class,
    'halls' => Hall::class,
    'seats' => Seat::class,
    'movies' => Movie::class,
    'now showing' => null,
    'coming soon' => null,
    'featured' => null,
    'showtimes' => Showtime::class,
    'offers' => Offer::class,
    'announcements' => Announcement::class,
    'reviews' => Review::class,
    'messages' => ContactMessage::class,
    'bookings' => Booking::class,
    'confirmed bookings' => null,
    'pending bookings' => null,
    'cancelled bookings' => null,
    'booking seats' => BookingSeat::class,
    'payments' => Payment::class,
    'successful payments' => null,
];

foreach ($models as $label => $class) {
    $count = match ($label) {
        'admins' => User::where('is_admin', true)->count(),
        'now showing' => Movie::where('is_now_showing', true)->count(),
        'coming soon' => Movie::where('is_coming_soon', true)->count(),
        'featured' => Movie::where('is_featured', true)->count(),
        'confirmed bookings' => Booking::where('status', 'confirmed')->count(),
        'pending bookings' => Booking::where('status', 'pending')->count(),
        'cancelled bookings' => Booking::where('status', 'cancelled')->count(),
        'successful payments' => Payment::where('status', 'success')->count(),
        default => $class::count(),
    };

    printf("%-20s %d\n", $label, $count);
}

// Show a sample showtime chain for debugging.
$showtime = Showtime::with(['movie', 'cinema', 'hall'])->first();
echo "\nSample showtime: {$showtime->movie->title} @ {$showtime->cinema->name} / {$showtime->hall->name} — {$showtime->show_date->toDateString()} {$showtime->start_time}\n";

$revenue = Booking::where('status', 'confirmed')->sum('total_amount');
echo 'Confirmed revenue: Rs. '.number_format((float) $revenue)."\n";
