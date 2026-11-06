<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(): View
    {
        $offers = Offer::query()->orderByDesc('created_at')->paginate(15);

        return view('admin.offers.index', compact('offers'));
    }

    public function create(): View
    {
        return view('admin.offers.form', ['offer' => new Offer]);
    }

    public function store(Request $request): RedirectResponse
    {
        Offer::create($this->validated($request));

        return redirect()->route('admin.offers.index')
            ->with('success', ui('admin_offer_created', 'Offer created.'));
    }

    public function edit(Offer $offer): View
    {
        return view('admin.offers.form', compact('offer'));
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        $offer->update($this->validated($request, $offer->id));

        return redirect()->route('admin.offers.index')
            ->with('success', ui('admin_offer_updated', 'Offer updated.'));
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $offer->delete();

        return redirect()->route('admin.offers.index')
            ->with('success', ui('admin_offer_deleted', 'Offer deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'required|string|max:1000',
            'code' => 'nullable|string|max:40|unique:offers,code'.($ignoreId ? ','.$ignoreId : ''),
            'badge' => 'nullable|string|max:30',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'image_url' => 'nullable|string|max:300',
            'is_active' => 'nullable|boolean',
        ], [
            'title.required' => ui('validation_offer_title', 'Please enter an offer title.'),
            'description.required' => ui('validation_offer_description', 'Please enter the offer details.'),
            'code.unique' => ui('validation_code_taken', 'That promo code is already in use.'),
        ]);

        $validated['code'] = $validated['code'] ? strtoupper(trim($validated['code'])) : null;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
