<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Movie discovery page with tabs, filters, search and sorting.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'tab' => 'nullable|in:all,now_showing,coming_soon,top_rated',
            'genre' => 'nullable|string|max:40',
            'language' => 'nullable|string|max:40',
            'show_date' => 'nullable|date_format:Y-m-d',
            'show_time' => 'nullable|date_format:H:i',
            'year' => 'nullable|integer|min:1950|max:2100',
            'cinema' => 'nullable|integer|exists:cinemas,id',
            'q' => 'nullable|string|max:80',
            'sort' => 'nullable|in:latest,title,rating,duration',
        ]);

        $tab = $validated['tab'] ?? 'all';
        $sort = $validated['sort'] ?? 'latest';

        $query = Movie::query()->active();

        match ($tab) {
            'now_showing' => $query->nowShowing(),
            'coming_soon' => $query->comingSoon(),
            'top_rated' => $query->nowShowing()->whereNotNull('rating')->orderByDesc('rating'),
            default => null,
        };

        if ($tab !== 'top_rated') {
            $this->applySort($query, $sort);
        }

        $this->applyFilters($query, $validated);

        $movies = $query->paginate(12)->withQueryString();

        $allGenres = $this->allGenres();

        return view('movies.index', compact('movies', 'tab', 'sort', 'allGenres'));
    }

    /**
     * Single movie page: banner, details, trailer, reviews and showtimes.
     */
    public function show(Request $request, Movie $movie): View
    {
        abort_unless($movie->is_active, 404);

        $showtimes = $movie->showtimes()
            ->active()
            ->upcoming()
            ->with(['cinema.city', 'hall'])
            ->get()
            ->when(selected_city(), function ($showtimes, $city) {
                return $showtimes->where('cinema.city_id', $city->id);
            });

        $reviews = $movie->reviews()
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        $averageRating = (float) round($movie->reviews()->avg('rating'), 1);
        $reviewCount = $movie->reviews()->count();

        $similar = Movie::query()
            ->active()
            ->whereKeyNot($movie->id)
            ->where(function ($query) use ($movie) {
                foreach ($movie->genres ?? [] as $genre) {
                    $query->orWhereJsonContains('genres', $genre);
                }
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $userReview = $request->user()?->reviews()->where('movie_id', $movie->id)->first();
        $isFavorite = $request->user()
            ? $request->user()->favoriteMovies()->whereKey($movie->id)->exists()
            : false;

        $groupedShowtimes = $showtimes->groupBy(fn ($showtime) => $showtime->show_date->toDateString());

        return view('movies.show', compact(
            'movie',
            'groupedShowtimes',
            'reviews',
            'averageRating',
            'reviewCount',
            'similar',
            'userReview',
            'isFavorite',
        ));
    }

    /**
     * Store a customer review for a movie.
     */
    public function storeReview(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:80',
            'body' => 'nullable|string|max:2000',
        ], [
            'rating.required' => ui('review_rating_required', 'Please choose a rating.'),
        ]);

        $request->user()->reviews()->updateOrCreate(
            ['movie_id' => $movie->id],
            $validated,
        );

        return back()->with('success', ui('review_saved', 'Thanks for your review!'));
    }

    /**
     * @param  Builder<Movie>  $query
     * @param  array<string, mixed>  $validated
     */
    private function applyFilters(Builder $query, array $validated): void
    {
        if (! empty($validated['genre'])) {
            $query->whereJsonContains('genres', $validated['genre']);
        }

        if (! empty($validated['language'])) {
            $query->where('language', $validated['language']);
        }

        if (! empty($validated['show_date']) || ! empty($validated['show_time'])) {
            $query->whereHas('showtimes', function ($builder) use ($validated) {
                $builder->upcoming()
                    ->when($validated['show_date'] ?? null, fn ($showtimes, $date) => $showtimes->whereDate('show_date', $date))
                    ->when($validated['show_time'] ?? null, fn ($showtimes, $time) => $showtimes->whereTime('start_time', $time.':00'));
            });
        }

        if (! empty($validated['year'])) {
            $query->whereYear('release_date', (int) $validated['year']);
        }

        if (! empty($validated['cinema'])) {
            $query->whereHas('showtimes', function ($builder) use ($validated) {
                $builder->active()->where('cinema_id', (int) $validated['cinema']);
            });
        }

        if (! empty($validated['q'])) {
            $term = '%'.$validated['q'].'%';
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', $term)
                    ->orWhere('title_np', 'like', $term)
                    ->orWhere('director', 'like', $term);
            });
        }
    }

    /**
     * @param  Builder<Movie>  $query
     */
    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'title' => $query->orderBy('title'),
            'rating' => $query->orderByDesc('rating'),
            'duration' => $query->orderByDesc('duration_minutes'),
            default => $query->orderByDesc('release_date'),
        };
    }

    /**
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
