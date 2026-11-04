<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_can_be_switched_to_nepali(): void
    {
        $this->post('/language', ['locale' => 'np'])->assertRedirect();
        $this->assertSame('np', session('locale'));

        $response = $this->get('/');
        $response->assertOk();

        $this->assertSame('np', app()->getLocale());
        $response->assertSee('चलचित्र');
    }

    public function test_language_can_be_switched_back_to_english(): void
    {
        $this->post('/language', ['locale' => 'np'])->assertRedirect();
        $this->post('/language', ['locale' => 'en'])->assertRedirect();

        $response = $this->get('/');
        $response->assertOk();

        $this->assertSame('en', app()->getLocale());
    }

    public function test_unsupported_language_is_rejected(): void
    {
        $this->from('/')
            ->post('/language', ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');

        $this->assertNull(session('locale'));
    }

    public function test_language_preference_is_saved_to_the_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/language', ['locale' => 'np'])->assertRedirect();

        $this->assertSame('np', $user->fresh()->preferred_language);
    }
}
