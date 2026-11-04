<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterSubscriptionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:254'],
        ], [
            'email.required' => ui('newsletter_email_required', 'Please enter your email address.'),
            'email.email' => ui('newsletter_email_invalid', 'Please enter a valid email address.'),
        ]);

        $subscription = NewsletterSubscription::query()->firstOrCreate([
            'email' => mb_strtolower($validated['email']),
        ]);

        return redirect()->back()->with(
            'success',
            $subscription->wasRecentlyCreated
                ? ui('newsletter_subscribed', 'Thanks for subscribing to CineGo updates.')
                : ui('newsletter_already_subscribed', 'This email is already subscribed to CineGo updates.'),
        );
    }
}
