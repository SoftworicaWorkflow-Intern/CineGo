<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShowtimeController extends Controller
{
    public function index(Request $request): View
    {
        $showtimes = Showtime::query()
            ->with(['movie', 'hall'])
            ->upcoming()
            ->latest('show_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.showtimes.index', compact('showtimes'));
    }

    public function create(): View
    {
        return view('admin.showtimes.form', [
            'showtime' => new Showtime,
            'movies' => Movie::active()->orderBy('title')->get(),
            'cinemas' => Cinema::active()->with('halls')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $showtime = Showtime::create($this->validated($request));

        return redirect()->route('admin.showtimes.index')
            ->with('success', ui('admin_showtime_created', 'Showtime created.'));
    }

    public function edit(Showtime $showtime): View
    {
        return view('admin.showtimes.form', [
            'showtime' => $showtime,
            'movies' => Movie::active()->orderBy('title')->get(),
            'cinemas' => Cinema::active()->with('halls')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Showtime $showtime): RedirectResponse
    {
        $showtime->update($this->validated($request, $showtime->id));

        return redirect()->route('admin.showtimes.index')
            ->with('success', ui('admin_showtime_updated', 'Showtime updated.'));
    }

    public function destroy(Showtime $showtime): RedirectResponse
    {
        $showtime->delete();

        return redirect()->route('admin.showtimes.index')
            ->with('success', ui('admin_showtime_deleted', 'Showtime deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'movie_id' => 'required|integer|exists:movies,id',
            'hall_id' => 'required|integer|exists:halls,id',
            'show_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'format' => 'required|in:2D,3D,IMAX,4DX',
            'language' => 'nullable|string|max:40',
            'price_normal' => 'required|numeric|min:0',
            'price_premium' => 'required|numeric|min:0',
            'price_recliner' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'movie_id.required' => ui('validation_movie', 'Please choose a movie.'),
            'hall_id.required' => ui('validation_hall', 'Please choose a screen.'),
            'show_date.after_or_equal' => ui('validation_future_date', 'The show date cannot be in the past.'),
            'price_normal.required' => ui('validation_price', 'Please enter the ticket price.'),
        ]);

        $hall = Hall::findOrFail($validated['hall_id']);
        $validated['cinema_id'] = $hall->cinema_id;

        $duplicate = Showtime::query()
            ->where('hall_id', $validated['hall_id'])
            ->where('show_date', $validated['show_date'])
            ->where('start_time', $validated['start_time'].':00')
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($duplicate) {
            abort(back()->withErrors(['start_time' => ui('validation_duplicate_show', 'That screen already has a show at this time.')]));
        }

        $validated['start_time'] = $validated['start_time'].':00';
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
