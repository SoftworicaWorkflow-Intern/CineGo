<?php

namespace App\Http\Controllers;

use App\Models\HeroSlide;
use App\Models\Movie;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * The CineGo homepage: hero slider, now showing, coming soon and value propositions.
     */
    public function index(): View
    {
        $heroItems = HeroSlide::query()->published()->get()->map(fn (HeroSlide $slide): array => [
            'desktop_image' => $slide->desktop_image_url() ?? '',
            'mobile_image' => $slide->mobile_image_url() ?? '',
            'label' => $slide->localized_label() ?? '',
            'movie_title' => $slide->localized_movie_title(),
            'title' => $slide->localized_title(),
            'description' => $slide->localized_description(),
            'language' => $slide->language ?? '',
            'duration' => $slide->duration_label(),
            'release_date' => $slide->release_date?->format('M j, Y') ?? '',
            'certificate' => '',
            'primary_text' => $slide->localized_primary_button_text(),
            'primary_url' => $slide->primary_button_url,
            'secondary_text' => $slide->localized_secondary_button_text() ?? '',
            'secondary_url' => $slide->secondary_button_url ?? '',
        ]);

        if ($heroItems->isEmpty() && ! HeroSlide::query()->exists()) {
            $heroItems = Movie::query()
                ->featured()
                ->nowShowing()
                ->orderBy('sort_order')
                ->orderByDesc('release_date')
                ->limit(5)
                ->get()
                ->map(fn (Movie $movie): array => [
                    'desktop_image' => $movie->banner_url ?: $movie->poster_url,
                    'mobile_image' => $movie->banner_url ?: $movie->poster_url,
                    'label' => ui('now_showing', 'Now Showing'),
                    'movie_title' => $movie->localised_title(),
                    'title' => $movie->localised_title(),
                    'description' => $movie->localised_synopsis(),
                    'language' => $movie->language,
                    'duration' => $movie->duration_label(),
                    'release_date' => $movie->release_date?->format('M j, Y') ?? '',
                    'certificate' => $movie->certificate ?? '',
                    'primary_text' => ui('book_tickets', 'Book Tickets'),
                    'primary_url' => route('movies.show', $movie),
                    'secondary_text' => ui('explore_movies', 'Explore Movies'),
                    'secondary_url' => route('movies.index'),
                ]);
        }

        $nowShowing = Movie::query()
            ->nowShowing()
            ->whereNotIn('slug', ['shikhar', 'maya-django'])
            ->orderByDesc('release_date')
            ->limit(1)
            ->get();

        $comingSoon = Movie::query()
            ->comingSoon()
            ->orderBy('release_date')
            ->limit(6)
            ->get();

        return view('home', compact(
            'heroItems',
            'nowShowing',
            'comingSoon',
        ));
    }
}
