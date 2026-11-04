<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\Movie;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    /**
     * All CineGo branches, optionally filtered by city.
     */
    public function index(Request $request): View
    {
        $selectedCity = $request->query('city');

        $query = Cinema::query()->active()->withCount([
            'halls',
            'showtimes' => fn ($builder) => $builder->upcoming(),
        ])->with('city')->orderBy('name');

        if ($selectedCity) {
            $query->whereHas('city', fn ($builder) => $builder->where('slug', $selectedCity));
        }

        $cinemas = $query->get();

        return view('cinemas.index', compact('cinemas', 'selectedCity'));
    }

    /**
     * Single branch: facilities, screens, movies and showtimes.
     */
    public function show(Request $request, Cinema $cinema): View
    {
        abort_unless($cinema->is_active, 404);

        $cinema->load('city');

        $movies = Movie::query()
            ->active()
            ->whereHas('showtimes', function ($builder) use ($cinema) {
                $builder->upcoming()->where('cinema_id', $cinema->id);
            })
            ->orderBy('title')
            ->get();

        $showtimes = $cinema->showtimes()
            ->active()
            ->upcoming()
            ->with(['movie', 'hall'])
            ->get();

        $groupedShowtimes = $showtimes->groupBy(fn ($showtime) => $showtime->show_date->toDateString());

        $mapQuery = urlencode($cinema->localised_address().', '.$cinema->city?->name.', Nepal');
        $directionsUrl = "https://www.google.com/maps/search/?api=1&query={$mapQuery}";

        return view('cinemas.show', compact(
            'cinema',
            'movies',
            'groupedShowtimes',
            'directionsUrl',
        ));
    }
}
