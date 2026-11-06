<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_regular_customer_cannot_access_admin(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->actingAs($customer)->get('/admin/movies')->assertForbidden();
    }

    public function test_admin_can_access_the_dashboard_and_crud_pages_except_cinemas(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $dashboard = $this->actingAs($admin)->get('/admin');
        $dashboard->assertOk()->assertDontSee('Cinemas');

        $this->actingAs($admin)->get('/admin/movies')->assertOk();
        $this->actingAs($admin)->get('/admin/cinemas')->assertNotFound();
        $showtimes = $this->actingAs($admin)->get('/admin/showtimes');
        $showtimes->assertOk()->assertDontSee('All cinemas');
        $this->actingAs($admin)->get('/admin/showtimes/create')
            ->assertOk()
            ->assertDontSee('Choose a cinema');
        $this->actingAs($admin)->get('/admin/bookings')->assertOk();
    }
}
