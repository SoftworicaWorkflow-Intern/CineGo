<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $movies = $this->actingAs($admin)->get('/admin/movies/create');
        $movies->assertOk()
            ->assertSee('<option value="Nepali"', false)
            ->assertDontSee('id="release_date"', false)
            ->assertSee('Description');
        $this->actingAs($admin)->get('/admin/cinemas')->assertNotFound();
        $this->actingAs($admin)->get('/admin/announcements')->assertNotFound();
        $showtimes = $this->actingAs($admin)->get('/admin/showtimes');
        $showtimes->assertOk()
            ->assertDontSee('All cinemas')
            ->assertDontSee('Screen');
        $showtimeForm = $this->actingAs($admin)->get('/admin/showtimes/create');
        $showtimeForm
            ->assertOk()
            ->assertDontSee('Choose a cinema')
            ->assertDontSee('Choose a screen')
            ->assertSee('id="start_time"', false)
            ->assertSee('step="60"', false);
        $this->actingAs($admin)->get('/admin/bookings')->assertOk();

        $settings = $this->actingAs($admin)->get('/admin/settings');
        $settings->assertOk()
            ->assertSee('Profile settings')
            ->assertSee('Security')
            ->assertDontSee('Preferences')
            ->assertDontSee('Preferred city')
            ->assertDontSee('नेपाली');

        $profile = $this->actingAs($admin)->get('/account/profile');
        $profile->assertOk()
            ->assertSee('Profile settings')
            ->assertSee('Security')
            ->assertDontSee('href="'.route('account.dashboard').'"')
            ->assertDontSee('href="'.route('account.bookings').'"')
            ->assertDontSee('href="'.route('account.favorites').'"')
            ->assertDontSee('href="'.route('account.payments').'"')
            ->assertDontSee('href="'.route('account.notifications').'"');
    }

    public function test_admin_can_upload_a_movie_poster(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Uploaded Poster Movie',
            'synopsis' => 'A movie with an uploaded poster.',
            'genres' => ['Drama'],
            'duration_minutes' => 120,
            'language' => 'English',
            'release_date' => now()->toDateString(),
            'poster' => UploadedFile::fake()->createWithContent(
                'poster.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('movies', ['title' => 'Uploaded Poster Movie']);
        $posterUrl = Movie::query()->where('title', 'Uploaded Poster Movie')->value('poster_url');

        Storage::disk('public')->assertExists(Str::after($posterUrl, '/storage/'));
    }

    public function test_admin_showtime_uses_the_first_active_hall_without_a_screen_field(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $movie = Movie::factory()->create();
        $hall = Hall::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.showtimes.store'), [
            'movie_id' => $movie->id,
            'show_date' => now()->addDay()->toDateString(),
            'start_time' => '19:00',
            'format' => '2D',
            'language' => $movie->language,
            'price_normal' => 450,
            'price_premium' => 650,
            'price_recliner' => 850,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.showtimes.index'));
        $this->assertDatabaseHas('showtimes', [
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'cinema_id' => $hall->cinema_id,
            'start_time' => '19:00:00',
        ]);
    }
}
