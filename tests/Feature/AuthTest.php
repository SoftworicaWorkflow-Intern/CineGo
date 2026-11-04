<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Aayush Thapa',
            'email' => 'aayush@example.com',
            'phone' => '9841000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'aayush@example.com']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Someone Else',
                'email' => 'taken@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = User::factory()->create();

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_account_pages_require_authentication(): void
    {
        $this->get('/account/dashboard')->assertRedirect(route('login'));
        $this->get('/account/bookings')->assertRedirect(route('login'));
        $this->get('/account/profile')->assertRedirect(route('login'));
    }

    public function test_authenticated_customer_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/account/dashboard')->assertOk();
        $this->actingAs($user)->get('/account/bookings')->assertOk();
        $this->actingAs($user)->get('/account/profile')->assertOk();
        $this->actingAs($user)->get('/account/settings')->assertOk();
    }
}
