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
        $heroItems = HeroSlide::query()
            ->with('movie')
            ->whereHas('movie')
            ->published()
            ->get()
            ->map(fn (HeroSlide $slide): array => [
                'movie' => $slide->movie,
                'desktop_image' => $slide->desktop_image_url() ?? '',
                'mobile_image' => $slide->mobile_image_url() ?? $slide->desktop_image_url() ?? '',
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
                    'movie' => $movie,
                    'desktop_image' => $movie->banner_url ?: $movie->poster_url,
                    'mobile_image' => $movie->banner_url ?: $movie->poster_url,
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
