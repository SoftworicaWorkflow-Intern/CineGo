<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UpcomingController extends Controller
{
    public function index(Request $request): View
    {
        $term = $request->query('q');

        $movies = Movie::query()
            ->comingSoon()
            ->when($term, fn ($query) => $query->where('title', 'like', '%'.$term.'%'))
            ->orderBy('release_date')
            ->paginate(12)
            ->withQueryString();

        return view('admin.upcoming.index', compact('movies', 'term'));
    }
}
