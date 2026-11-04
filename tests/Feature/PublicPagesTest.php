<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\City;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_for_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('img/cinego-logo.png');
    }

    public function test_header_hides_city_selector_and_cinema_navigation_link(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="header-city"', false)
            ->assertDontSee('id="mobile-city"', false)
            ->assertDontSee(route('cinemas.index').'" class="nav-link', false);
    }

    public function test_about_page_shows_kawasoti_location(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('Today CineGo connects audiences with Kawasoti in English and Nepali, on any device. We are just getting started.')
            ->assertSee('Kawasoti-3, Nepal')
            ->assertDontSee('Jamal, Kathmandu 44600, Nepal');
    }

    public function test_public_listing_and_content_pages_render(): void
    {
        $pages = [
            '/movies',
            '/cinemas',
            '/offers',
            '/about',
            '/contact',
            '/terms',
            '/privacy',
            '/refund-policy',
            '/login',
            '/register',
            '/forgot-password',
        ];

        foreach ($pages as $page) {
            $this->get($page)->assertOk();
        }
    }

    public function test_movie_detail_page_renders(): void
    {
        $movie = Movie::factory()->create();
        $city = City::factory()->create(['name' => 'Hidden City']);
        $cinema = Cinema::factory()->create([
            'city_id' => $city->id,
            'address' => '42 Hidden Street',
        ]);
        $hall = Hall::factory()->create(['cinema_id' => $cinema->id]);
        $showtime = Showtime::factory()->create([
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'hall_id' => $hall->id,
            'show_date' => now()->addDay()->toDateString(),
            'start_time' => '16:00:00',
        ]);

        $this->get('/movies/'.$movie->slug)
            ->assertOk()
            ->assertSee($movie->title)
            ->assertSee('4:00 PM')
            ->assertSee(route('booking.seats', $showtime))
            ->assertDontSee($cinema->name)
            ->assertDontSee('42 Hidden Street')
            ->assertDontSee('Hidden City');
    }

    public function test_cinema_detail_page_renders(): void
    {
        $cinema = Cinema::factory()->create();

        $this->get('/cinemas/'.$cinema->slug)
            ->assertOk()
            ->assertSee($cinema->name);
    }

    public function test_movie_search_returns_results_page(): void
    {
        $movie = Movie::factory()->create(['title' => 'Everest Rising']);

        $this->get('/movies?q=Everest')
            ->assertOk()
            ->assertSee('Everest Rising');

        $this->get('/movies?q=no-such-film-anywhere')
            ->assertOk();
    }

    public function test_unknown_movie_returns_404(): void
    {
        $this->get('/movies/no-such-film')->assertNotFound();
    }

    public function test_contact_form_stores_message(): void
    {
        $this->post('/contact', [
            'name' => 'Sita Sharma',
            'email' => 'sita@example.com',
            'subject' => 'Group booking',
            'message' => 'Do you offer group discounts for 20 people?',
        ])->assertRedirect(route('pages.contact'));

        $this->assertDatabaseHas('contact_messages', ['email' => 'sita@example.com']);
    }

    public function test_contact_form_requires_subject_and_message(): void
    {
        $this->from('/contact')
            ->post('/contact', ['name' => 'Sita', 'email' => 'sita@example.com'])
            ->assertSessionHasErrors(['subject', 'message']);
    }
}
