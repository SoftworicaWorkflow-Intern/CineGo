<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_email_is_saved_as_a_newsletter_subscription(): void
    {
        $response = $this->from('/')
            ->post(route('newsletter-subscriptions.store'), [
                'email' => 'MovieFan@example.com',
            ]);

        $response->assertRedirect('/')
            ->assertSessionHas('success', ui('newsletter_subscribed', 'Thanks for subscribing to CineGo updates.'));

        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'moviefan@example.com',
        ]);
    }

    public function test_invalid_email_is_rejected_without_saving_a_subscription(): void
    {
        $response = $this->from('/')
            ->post(route('newsletter-subscriptions.store'), [
                'email' => 'not-an-email',
            ]);

        $response->assertRedirect('/')
            ->assertSessionHasErrors([
                'email' => ui('newsletter_email_invalid', 'Please enter a valid email address.'),
            ]);

        $this->assertDatabaseCount('newsletter_subscriptions', 0);
    }

    public function test_missing_email_is_rejected_without_saving_a_subscription(): void
    {
        $response = $this->from('/')
            ->post(route('newsletter-subscriptions.store'));

        $response->assertRedirect('/')
            ->assertSessionHasErrors([
                'email' => ui('newsletter_email_required', 'Please enter your email address.'),
            ]);

        $this->assertDatabaseCount('newsletter_subscriptions', 0);
    }

    public function test_repeated_email_does_not_create_a_duplicate_subscription(): void
    {
        NewsletterSubscription::query()->create([
            'email' => 'moviefan@example.com',
        ]);

        $response = $this->from('/')
            ->post(route('newsletter-subscriptions.store'), [
                'email' => 'MovieFan@example.com',
            ]);

        $response->assertRedirect('/')
            ->assertSessionHas('success', ui('newsletter_already_subscribed', 'This email is already subscribed to CineGo updates.'));

        $this->assertDatabaseCount('newsletter_subscriptions', 1);
    }

    public function test_footer_newsletter_copy_uses_the_selected_nepali_locale(): void
    {
        $response = $this->withSession(['locale' => 'np'])->get('/');

        $response->assertOk()
            ->assertSee('CineGo सँग नयाँ जानकारी पाउनुहोस्')
            ->assertSee('सदस्यता लिनुहोस्')
            ->assertSee('कानुनी लिङ्कहरू');
    }
}
