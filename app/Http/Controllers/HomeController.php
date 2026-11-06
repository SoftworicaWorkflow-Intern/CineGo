<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * The CineGo homepage: hero slider, now showing, coming soon and value propositions.
     */
    public function index(): View
    {
        $featured = Movie::query()
            ->featured()
            ->nowShowing()
            ->orderBy('sort_order')
            ->orderByDesc('release_date')
            ->limit(5)
            ->get();

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
            'featured',
            'nowShowing',
            'comingSoon',
        ));
    }
}
