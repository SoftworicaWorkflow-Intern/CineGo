<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\City;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CinemaController extends Controller
{
    public function index(): View
    {
        $cinemas = Cinema::query()->with('city')->withCount('halls')->orderBy('name')->get();

        return view('admin.cinemas.index', compact('cinemas'));
    }

    public function create(): View
    {
        return view('admin.cinemas.form', [
            'cinema' => new Cinema,
            'cities' => City::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cinema = Cinema::create($this->validated($request));

        return redirect()->route('admin.cinemas.edit', $cinema)
            ->with('success', ui('admin_cinema_created', 'Cinema branch created.'));
    }

    public function edit(Cinema $cinema): View
    {
        $cinema->load('halls');

        return view('admin.cinemas.form', [
            'cinema' => $cinema,
            'cities' => City::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Cinema $cinema): RedirectResponse
    {
        $cinema->update($this->validated($request));

        return redirect()->route('admin.cinemas.edit', $cinema)
            ->with('success', ui('admin_cinema_updated', 'Cinema branch updated.'));
    }

    public function destroy(Cinema $cinema): RedirectResponse
    {
        $cinema->delete();

        return redirect()->route('admin.cinemas.index')
            ->with('success', ui('admin_cinema_deleted', 'Cinema branch deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'city_id' => 'required|integer|exists:cities,id',
            'name' => 'required|string|max:120',
            'name_np' => 'nullable|string|max:120',
            'address' => 'required|string|max:200',
            'address_np' => 'nullable|string|max:200',
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:160',
            'description' => 'nullable|string|max:2000',
            'description_np' => 'nullable|string|max:2000',
            'facilities' => 'nullable|string|max:500',
            'image_url' => 'nullable|string|max:300',
            'is_active' => 'nullable|boolean',
        ], [
            'city_id.required' => ui('validation_city', 'Please choose a city.'),
            'name.required' => ui('validation_name_branch', 'Please enter the branch name.'),
            'address.required' => ui('validation_address', 'Please enter the address.'),
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name'], $request->route('cinema')?->id);
        $validated['facilities'] = $this->parseFacilities($request->input('facilities'));
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * "Parking, Snacks, 3D" → ['Parking', 'Snacks', '3D']
     *
     * @return list<string>|null
     */
    private function parseFacilities(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'cinema';
        $slug = $base;
        $suffix = 1;

        while (Cinema::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
