<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    /**
     * Switch the interface language (English / नेपाली).
     */
    public function setLanguage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => 'required|in:en,np',
        ]);

        session(['locale' => $validated['locale']]);

        if ($request->user()) {
            $request->user()->update(['preferred_language' => $validated['locale']]);
        }

        return back();
    }

    /**
     * Choose the city used to filter branches and showtimes.
     */
    public function setCity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'city_id' => 'nullable|integer|exists:cities,id',
        ]);

        if (! empty($validated['city_id'])) {
            session(['city_id' => (int) $validated['city_id']]);
        } else {
            session()->forget('city_id');
        }

        return back();
    }
}
