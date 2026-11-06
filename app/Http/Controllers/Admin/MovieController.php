<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $validated = $this->validated($request);
        $validated['poster_url'] = $this->storePoster($request);
        $movie = Movie::create($validated);

        return redirect()->route('admin.movies.edit', $movie)
            ->with('success', ui('admin_movie_created', 'Movie created.'));
    }

    public function edit(Movie $movie): View
    {
        return view('admin.movies.form', compact('movie'));
    }

    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('poster')) {
            $validated['poster_url'] = $this->storePoster($request);
            $this->deleteStoredPoster($movie->poster_url);
        } else {
            $validated['poster_url'] = $movie->poster_url;
        }

        $movie->update($validated);

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
            'synopsis' => 'required|string|max:4000',
            'genres' => 'required|array|min:1',
            'genres.*' => 'string|max:40',
            'duration_minutes' => 'required|integer|min:1|max:600',
            'language' => 'required|string|max:40',
            'release_date' => 'nullable|date_format:Y-m-d',
            'certificate' => 'nullable|string|max:10',
            'poster' => [
                $request->route('movie') instanceof Movie ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'trailer_url' => 'nullable|string|max:300',
            'director' => 'nullable|string|max:120',
            'cast' => 'nullable|string|max:500',
        ], [
            'title.required' => ui('validation_title', 'Please enter the movie title.'),
            'synopsis.required' => ui('validation_synopsis', 'Please enter a description.'),
            'genres.required' => ui('validation_genres', 'Please choose at least one genre.'),
            'poster.required' => ui('validation_poster', 'Please upload a poster image.'),
        ]);

        $validated['slug'] = $this->uniqueSlug($request->input('title'), $request->route('movie')?->id);
        $movie = $request->route('movie');
        $validated['release_date'] = $validated['release_date']
            ?? ($movie instanceof Movie ? $movie->release_date?->toDateString() : today()->toDateString());
        $isComingSoon = now()->toDateString() < $validated['release_date'];
        $validated['is_now_showing'] = ! $isComingSoon;
        $validated['is_coming_soon'] = $isComingSoon;
        $validated['is_featured'] = $movie instanceof Movie ? $movie->is_featured : false;
        $validated['is_active'] = $movie instanceof Movie ? $movie->is_active : true;

        return $validated;
    }

    private function storePoster(Request $request): string
    {
        return Storage::disk('public')->url($request->file('poster')->store('posters', 'public'));
    }

    private function deleteStoredPoster(?string $posterUrl): void
    {
        if (! $posterUrl || ! Str::startsWith($posterUrl, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($posterUrl, '/storage/'));
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
