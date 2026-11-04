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
            ->assertSee('img/cinego-logo.png')
            ->assertSee('href="'.asset('favicon.png').'"', false)
            ->assertSee('type="image/png" sizes="96x96"', false)
            ->assertSee(ui('footer_tagline', 'Your Movie. Your Moment. Your CineGo.'), false)
            ->assertSee(route('newsletter-subscriptions.store'), false)
            ->assertSee(ui('newsletter_title', 'Stay Updated with CineGo'), false)
            ->assertSee(route('movies.index', ['tab' => 'now_showing']), false)
            ->assertSee(route('movies.index', ['tab' => 'coming_soon']), false)
            ->assertDontSee('href="/cinemas"', false)
            ->assertSee('Kawasoti-3, Nepal')
            ->assertSee('support@cinego.com.np');
    }

    public function test_trailer_modal_uses_flex_alignment_to_center_the_video(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('class="fixed inset-0 z-50 hidden flex items-center justify-center', false);
    }

    public function test_homepage_uses_the_uploaded_poster_as_a_full_viewport_hero(): void
    {
        $movie = Movie::factory()->featured()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('img/hero/ghar-jwai.png', false)
            ->assertSee('img/hero/unko-sweater.png', false)
            ->assertSee('img/hero/bha.png', false)
            ->assertSee('Against a Himalayan winter, a hand-knit sweater weaves together love, longing, and the feeling of home.')
            ->assertSee('A gripping Nepali drama of courage and destiny, where the past shapes every choice and every journey.')
            ->assertSee('data-hero-duration="1h 48m"', false)
            ->assertSee('data-hero-language="Nepali"', false)
            ->assertSee('data-hero-release-date="Nov 2, 2026"', false)
            ->assertSee('data-hero-book-title="Gharjwai"', false)
            ->assertSee('min-h-[100vh] min-h-[100svh]', false)
            ->assertSee('bg-cover bg-center bg-no-repeat', false)
            ->assertSee($movie->localised_title())
            ->assertSee('Book Tickets')
            ->assertSee('data-no-scroll-reveal', false)
            ->assertSee('data-hero-pagination', false)
            ->assertSee('data-hero-page', false)
            ->assertDontSee('data-slider', false)
            ->assertDontSee('data-hero-poster-slider', false);
    }

    public function test_homepage_search_uses_a_short_movie_placeholder(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('placeholder="Search movie"', false)
            ->assertDontSee('genre, language or cinema');
    }

    public function test_homepage_shows_a_trailer_action_for_each_movie(): void
    {
        $movieWithTrailer = Movie::factory()->create([
            'title' => 'Movie with trailer',
            'trailer_url' => 'https://www.youtube.com/embed/example',
            'is_featured' => false,
        ]);
        $movieWithoutTrailer = Movie::factory()->create([
            'title' => 'Movie without trailer',
            'trailer_url' => null,
            'is_featured' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('data-trailer="https://www.youtube.com/embed/example"', false)
            ->assertSee('Watch Trailer')
            ->assertSee('disabled', false)
            ->assertSee($movieWithTrailer->title)
            ->assertSee($movieWithoutTrailer->title);
    }

    public function test_movies_page_shows_booking_and_trailer_actions_for_coming_soon_movies(): void
    {
        $movie = Movie::factory()->comingSoon()->create([
            'title' => 'Coming Soon with Trailer',
            'trailer_url' => 'https://www.youtube.com/embed/coming-soon',
        ]);

        $this->get(route('movies.index'))
            ->assertOk()
            ->assertSee('href="'.route('movies.show', $movie).'#showtimes"', false)
            ->assertSee('data-trailer="https://www.youtube.com/embed/coming-soon"', false)
            ->assertSee('Book Tickets')
            ->assertSee('Watch Trailer');
    }

    public function test_movies_page_shows_only_movie_genre_date_and_showtime_filters(): void
    {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertSee('placeholder="Search movies"', false)
            ->assertSee('name="genre"', false)
            ->assertSee('type="date" name="show_date"', false)
            ->assertSee('type="time" name="show_time"', false)
            ->assertSee('lg:grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)_minmax(0,1.2fr)_minmax(0,1.2fr)_auto_auto]', false)
            ->assertSee('class="btn-primary h-12 whitespace-nowrap px-5"', false)
            ->assertDontSee('name="language"', false)
            ->assertDontSee('name="year"', false)
            ->assertDontSee('name="cinema"', false)
            ->assertDontSee('name="sort"', false);
    }

    public function test_movie_filters_match_genre_date_and_showtime(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(12));

        $matchingMovie = Movie::factory()->create([
            'title' => 'Matching Thriller',
            'genres' => ['Thriller'],
        ]);
        $wrongTimeMovie = Movie::factory()->create([
            'title' => 'Wrong Time Thriller',
            'genres' => ['Thriller'],
        ]);
        $wrongGenreMovie = Movie::factory()->create([
            'title' => 'Wrong Genre Comedy',
            'genres' => ['Comedy'],
        ]);

        Showtime::factory()->for($matchingMovie)->create([
            'show_date' => now()->addDay()->toDateString(),
            'start_time' => '18:00:00',
        ]);
        Showtime::factory()->for($wrongTimeMovie)->create([
            'show_date' => now()->addDay()->toDateString(),
            'start_time' => '19:00:00',
        ]);
        Showtime::factory()->for($wrongGenreMovie)->create([
            'show_date' => now()->addDay()->toDateString(),
            'start_time' => '18:00:00',
        ]);

        $this->get(route('movies.index', [
            'genre' => 'Thriller',
            'show_date' => now()->addDay()->toDateString(),
            'show_time' => '18:00',
        ]))
            ->assertOk()
            ->assertSee('Matching Thriller')
            ->assertDontSee('Wrong Time Thriller')
            ->assertDontSee('Wrong Genre Comedy');
    }

    public function test_header_hides_city_selector_and_cinema_navigation_link(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="header-city"', false)
            ->assertDontSee('id="mobile-city"', false)
            ->assertDontSee('name="locale"', false)
            ->assertDontSee('href="/cinemas"', false);
    }

    public function test_about_page_shows_kawasoti_location(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('Today CineGo connects audiences with Kawasoti in English and Nepali, on any device. We are just getting started.')
            ->assertSee('Kawasoti-3, Nepal')
            ->assertSee('https://maps.google.com/maps?q=Kawasoti-3%2C%20Nepal', false)
            ->assertSee('title="Map showing Kawasoti-3, Nepal"', false)
            ->assertDontSee('Jamal, Kathmandu 44600, Nepal');
    }

    public function test_public_listing_and_content_pages_render(): void
    {
        $pages = [
            '/movies',
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
            ->assertSee('href="'.route('booking.seats', $showtime).'" class="btn-primary btn-block"', false)
            ->assertDontSee($cinema->name)
            ->assertDontSee('42 Hidden Street')
            ->assertDontSee('Hidden City');
    }

    public function test_movie_detail_renders_a_seat_link_for_each_showtime_date(): void
    {
        $movie = Movie::factory()->create();
        $cinema = Cinema::factory()->create();
        $hall = Hall::factory()->create(['cinema_id' => $cinema->id]);
        $showtimeAttributes = [
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'hall_id' => $hall->id,
        ];
        $firstShowtime = Showtime::factory()->create([
            ...$showtimeAttributes,
            'show_date' => now()->addDay()->toDateString(),
            'start_time' => '19:00:00',
        ]);
        $secondShowtime = Showtime::factory()->create([
            ...$showtimeAttributes,
            'show_date' => now()->addDays(2)->toDateString(),
            'start_time' => '16:00:00',
        ]);

        $this->get(route('movies.show', $movie))
            ->assertOk()
            ->assertSee('data-booking-link href="'.route('booking.seats', $firstShowtime).'"', false)
            ->assertSee('data-booking-url="'.route('booking.seats', $firstShowtime).'"', false)
            ->assertSee('data-booking-url="'.route('booking.seats', $secondShowtime).'"', false);
    }

    public function test_movie_detail_does_not_link_to_showtimes_that_started_today(): void
    {
        $this->travelTo(now()->setTime(16, 0));

        $movie = Movie::factory()->create();
        $cinema = Cinema::factory()->create();
        $hall = Hall::factory()->create(['cinema_id' => $cinema->id]);
        $showtimeAttributes = [
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'hall_id' => $hall->id,
            'show_date' => today()->toDateString(),
        ];
        $pastShowtime = Showtime::factory()->create([
            ...$showtimeAttributes,
            'start_time' => '15:00:00',
        ]);
        $upcomingShowtime = Showtime::factory()->create([
            ...$showtimeAttributes,
            'start_time' => '17:00:00',
        ]);

        $this->get(route('movies.show', $movie))
            ->assertOk()
            ->assertDontSee(route('booking.seats', $pastShowtime))
            ->assertSee(route('booking.seats', $upcomingShowtime));
    }

    public function test_public_cinema_directory_urls_redirect_to_movies(): void
    {
        $this->get('/cinemas')
            ->assertRedirect(route('movies.index'));

        $this->get('/cinemas/old-branch')
            ->assertRedirect(route('movies.index'));
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
