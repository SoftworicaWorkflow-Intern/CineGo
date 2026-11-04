<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Offer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class HomeController extends Controller
{
    /**
     * The CineGo homepage: hero slider, now showing, coming soon, genres,
     * cinemas, offers and the value propositions.
     */
    public function index(): View
    {
        $featured = Movie::query()
            ->featured()
            ->nowShowing()
            ->orderByDesc('release_date')
            ->limit(5)
            ->get();

        $nowShowing = Movie::query()
            ->nowShowing()
            ->orderByDesc('release_date')
            ->limit(8)
            ->get();

        $comingSoon = Movie::query()
            ->comingSoon()
            ->orderBy('release_date')
            ->limit(6)
            ->get();

        $cinemas = $this->cinemaQuery()->limit(6)->get();

        $offers = Offer::query()
            ->currentlyValid()
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $genres = $this->genreCounts();
        $genreOptions = $this->allGenres();
        $languages = Movie::query()->active()->select('language')->distinct()->orderBy('language')->pluck('language');

        return view('home', compact(
            'featured',
            'nowShowing',
            'comingSoon',
            'cinemas',
            'offers',
            'genres',
            'genreOptions',
            'languages',
        ));
    }

    /**
     * @return Builder<Cinema>
     */
    private function cinemaQuery(): Builder
    {
        $query = Cinema::query()->active()->with('city');

        if ($city = selected_city()) {
            $query->where('city_id', $city->id);
        }

        return $query->orderBy('name');
    }

    /**
     * Genre cards with the number of matching movies.
     *
     * @return list<array{genre: string, count: int}>
     */
    private function genreCounts(): array
    {
        $counts = [];

        Movie::query()->active()->select('genres')->get()->each(function (Movie $movie) use (&$counts) {
            foreach ($movie->genres ?? [] as $genre) {
                $counts[$genre] = ($counts[$genre] ?? 0) + 1;
            }
        });

        arsort($counts);

        return collect($counts)
            ->map(fn (int $count, string $genre) => ['genre' => $genre, 'count' => $count])
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * Every genre in the catalogue, ordered for the search filters.
     *
     * @return list<string>
     */
    private function allGenres(): array
    {
        $genres = [];

        Movie::query()->active()->select('genres')->get()->each(function (Movie $movie) use (&$genres) {
            foreach ($movie->genres ?? [] as $genre) {
                $genres[$genre] = true;
            }
        });

        $preferred = ['Action', 'Comedy', 'Drama', 'Romance', 'Horror', 'Thriller', 'Animation', 'Adventure'];
        $known = array_values(array_intersect($preferred, array_keys($genres)));
        $rest = array_values(array_diff(array_keys($genres), $known));

        return array_merge($known, $rest);
    }
}
