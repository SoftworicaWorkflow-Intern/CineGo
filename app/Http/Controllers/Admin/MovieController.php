<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    /**
     * Movie catalogue management.
     */
    public function index(Request $request): View
    {
        $term = $request->query('q');

        $movies = Movie::query()
            ->when($term, fn ($query) => $query->where('title', 'like', '%'.$term.'%'))
            ->orderByDesc('release_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.movies.index', compact('movies', 'term'));
    }

    public function create(): View
    {
        return view('admin.movies.form', ['movie' => new Movie]);
    }

    public function store(Request $request): RedirectResponse
    {
        $movie = Movie::create($this->validated($request));

        return redirect()->route('admin.movies.edit', $movie)
            ->with('success', ui('admin_movie_created', 'Movie created.'));
    }

    public function edit(Movie $movie): View
    {
        return view('admin.movies.form', compact('movie'));
    }

    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $movie->update($this->validated($request));

        return redirect()->route('admin.movies.edit', $movie)
            ->with('success', ui('admin_movie_updated', 'Movie updated.'));
    }

    public function destroy(Movie $movie): RedirectResponse
    {
        $movie->delete();

        return redirect()->route('admin.movies.index')
            ->with('success', ui('admin_movie_deleted', 'Movie deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:160',
            'title_np' => 'nullable|string|max:160',
            'synopsis' => 'required|string|max:4000',
            'synopsis_np' => 'nullable|string|max:4000',
            'genres' => 'required|array|min:1',
            'genres.*' => 'string|max:40',
            'duration_minutes' => 'required|integer|min:1|max:600',
            'language' => 'required|string|max:40',
            'release_date' => 'required|date',
            'certificate' => 'nullable|string|max:10',
            'rating' => 'nullable|numeric|min:0|max:10',
            'poster_url' => 'required|string|max:300',
            'banner_url' => 'nullable|string|max:300',
            'trailer_url' => 'nullable|string|max:300',
            'director' => 'nullable|string|max:120',
            'cast' => 'nullable|string|max:500',
            'is_now_showing' => 'nullable|boolean',
            'is_coming_soon' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'title.required' => ui('validation_title', 'Please enter the movie title.'),
            'synopsis.required' => ui('validation_synopsis', 'Please enter a storyline.'),
            'genres.required' => ui('validation_genres', 'Please choose at least one genre.'),
            'poster_url.required' => ui('validation_poster', 'Please provide a poster image path.'),
        ]);

        $validated['slug'] = $this->uniqueSlug($request->input('title'), $request->route('movie')?->id);
        $validated['is_now_showing'] = $request->boolean('is_now_showing');
        $validated['is_coming_soon'] = $request->boolean('is_coming_soon');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'movie';
        $slug = $base;
        $suffix = 1;

        while (Movie::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
