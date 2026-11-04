<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    /**
     * Public list of current discounts and promo campaigns.
     */
    public function index(): View
    {
        $offers = Offer::query()
            ->currentlyValid()
            ->orderByDesc('discount_value')
            ->get();

        return view('offers.index', compact('offers'));
    }

    /**
     * Validate a promo code at checkout (AJAX).
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:40',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $offer = Offer::query()
            ->currentlyValid()
            ->where('code', 'like', trim($validated['code']))
            ->first();

        if (! $offer) {
            return response()->json([
                'valid' => false,
                'message' => ui('promo_invalid', 'That promo code is not valid.'),
            ], 422);
        }

        $subtotal = (float) $validated['subtotal'];
        $discount = $offer->discount_for($subtotal);

        if ($discount <= 0 && $offer->min_amount !== null && $subtotal < $offer->min_amount) {
            return response()->json([
                'valid' => false,
                'message' => ui('promo_min_amount', 'This code requires a minimum order of Rs. ').number_format($offer->min_amount).'.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'code' => $offer->code,
            'label' => $offer->badge_label(),
            'title' => $offer->localised_title(),
            'discount' => round($discount, 2),
            'message' => ui('promo_applied', 'Promo code applied.'),
        ]);
    }
}
