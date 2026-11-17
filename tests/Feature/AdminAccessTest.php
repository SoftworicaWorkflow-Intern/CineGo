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
        $this->travelTo('2026-11-17 12:00:00');
        $admin = User::factory()->create(['is_admin' => true]);

        $dashboard = $this->actingAs($admin)->get('/admin');
        $dashboard->assertOk()->assertDontSee('Cinemas');

        $movieIndex = $this->actingAs($admin)->get('/admin/movies');
        $movieIndex->assertOk()->assertDontSee(ui('status', 'Status'));

        $movies = $this->actingAs($admin)->get('/admin/movies/create');
        $movies->assertOk()
            ->assertSee('<option value="Nepali"', false)
            ->assertSee('<option value="now_showing"', false)
            ->assertSee('<option value="coming_soon"', false)
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
        $bookingIndex = $this->actingAs($admin)->get('/admin/bookings');
        $bookingIndex->assertOk()->assertDontSee(ui('status', 'Status'));

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
            ->assertDontSee('Favourite')
            ->assertDontSee('href="'.route('account.payments').'"')
            ->assertDontSee('href="'.route('account.notifications').'"');
    }

    public function test_admin_can_upload_a_movie_poster(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $posterContents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Uploaded Poster Movie',
            'synopsis' => 'A movie with an uploaded poster.',
            'genres' => ['Drama'],
            'duration_minutes' => 120,
            'language' => 'English',
            'release_date' => today()->toDateString(),
            'poster' => UploadedFile::fake()->createWithContent('poster.png', $posterContents.str_repeat('0', 3 * 1024 * 1024)),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('movies', [
            'title' => 'Uploaded Poster Movie',
            'is_now_showing' => true,
            'is_coming_soon' => false,
        ]);
        $this->assertSame(
            today()->toDateString(),
            Movie::query()->where('title', 'Uploaded Poster Movie')->firstOrFail()->release_date->toDateString(),
        );
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
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.showtimes.index'));
        $this->assertDatabaseHas('showtimes', [
            'movie_id' => $movie->id,
            'hall_id' => $hall->id,
            'cinema_id' => $hall->cinema_id,
            'start_time' => '19:00:00',
            'price_normal' => 450,
        ]);

        $this->get(route('admin.showtimes.create'))
            ->assertOk()
            ->assertSee('id="price_normal"', false)
            ->assertDontSee('Premium')
            ->assertDontSee('Recliner');
    }

    public function test_admin_saves_a_future_release_as_coming_soon(): void
    {
        Storage::fake('public');
        $this->travelTo('2026-11-06 12:00:00');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Future Release Movie',
            'synopsis' => 'A movie that releases in 2027.',
            'genres' => ['Drama'],
            'duration_minutes' => 120,
            'language' => 'English',
            'release_date' => '2027-01-01',
            'poster' => UploadedFile::fake()->createWithContent(
                'poster.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('movies', [
            'title' => 'Future Release Movie',
            'is_now_showing' => false,
            'is_coming_soon' => true,
        ]);
        $this->assertSame(
            '2027-01-01',
            Movie::query()->where('title', 'Future Release Movie')->firstOrFail()->release_date->toDateString(),
        );

        $this->get('/')
            ->assertOk()
            ->assertSee('Next Releases')
            ->assertSee('Future Release Movie');

        $this->get(route('movies.index', ['tab' => 'coming_soon']))
            ->assertOk()
            ->assertSee('Future Release Movie');
    }

    public function test_admin_can_choose_movie_status_independently_of_release_date(): void
    {
        Storage::fake('public');
        $this->travelTo('2026-11-06 12:00:00');
        $admin = User::factory()->create(['is_admin' => true]);

        $nowShowingResponse = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Selected Now Showing Movie',
            'synopsis' => 'An upcoming movie chosen as now showing.',
            'genres' => ['Drama'],
            'duration_minutes' => 120,
            'language' => 'English',
            'movie_status' => 'now_showing',
            'release_date' => '2027-01-01',
            'poster' => UploadedFile::fake()->createWithContent(
                'now-showing-poster.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ]);
        $nowShowingResponse->assertRedirect();
        $this->assertDatabaseHas('movies', [
            'title' => 'Selected Now Showing Movie',
            'is_now_showing' => true,
            'is_coming_soon' => false,
        ]);

        $comingSoonResponse = $this->post(route('admin.movies.store'), [
            'title' => 'Selected Coming Soon Movie',
            'synopsis' => 'A movie chosen as coming soon.',
            'genres' => ['Drama'],
            'duration_minutes' => 120,
            'language' => 'English',
            'movie_status' => 'coming_soon',
            'release_date' => today()->toDateString(),
            'poster' => UploadedFile::fake()->createWithContent(
                'coming-soon-poster.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ]);
        $comingSoonResponse->assertRedirect();
        $this->assertDatabaseHas('movies', [
            'title' => 'Selected Coming Soon Movie',
            'is_now_showing' => false,
            'is_coming_soon' => true,
        ]);
    }

    public function test_admin_updates_movie_status_when_release_date_changes(): void
    {
        $this->travelTo('2026-11-06 12:00:00');
        $admin = User::factory()->create(['is_admin' => true]);
        $movie = Movie::factory()->comingSoon()->create();

        $response = $this->actingAs($admin)->put(route('admin.movies.update', $movie), [
            'title' => $movie->title,
            'synopsis' => $movie->synopsis,
            'genres' => $movie->genres,
            'duration_minutes' => $movie->duration_minutes,
            'language' => $movie->language,
            'release_date' => '2026-11-06',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('movies', [
            'id' => $movie->id,
            'is_now_showing' => true,
            'is_coming_soon' => false,
        ]);
        $this->assertSame('2026-11-06', $movie->fresh()->release_date->toDateString());
    }
}
