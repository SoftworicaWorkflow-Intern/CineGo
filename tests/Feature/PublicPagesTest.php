<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\City;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Showtime;
use Database\Seeders\KawasotiDemoSeeder;
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
            ->assertSee(ui('contact_us', 'Contact Us'), false)
            ->assertSee('img/softworica.png', false)
            ->assertSee('Designed &amp; developed by', false)
            ->assertSee(route('newsletter-subscriptions.store'), false)
            ->assertSee(ui('newsletter_title', 'Stay Updated with CineGo'), false)
            ->assertSee(route('movies.index', ['tab' => 'now_showing']), false)
            ->assertSee(route('movies.index', ['tab' => 'coming_soon']), false)
            ->assertDontSee('href="'.route('offers.index').'"', false)
            ->assertDontSee('How to Book Tickets')
            ->assertDontSee('Payment Methods')
            ->assertDontSee('Crafted for Nepali cinema lovers.')
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

    public function test_homepage_uses_featured_movies_for_hero_slides(): void
    {
        $movie = Movie::factory()->featured()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee($movie->banner_url, false)
            ->assertSee('min-h-[min(760px,100svh)]', false)
            ->assertSee('cg-home-hero-art cg-home-hero-art-active', false)
            ->assertSee('data-hero-pagination', false)
            ->assertSee('data-hero-actions', false)
            ->assertSee('data-hero-synopsis', false)
            ->assertSee($movie->localised_synopsis())
            ->assertSee('data-no-scroll-reveal', false)
            ->assertSee('data-hero-page', false)
            ->assertDontSee('data-slider', false)
            ->assertDontSee('data-hero-poster-slider', false);
    }

    public function test_homepage_omits_the_movie_search_form(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="home-search"', false)
            ->assertDontSee('id="home-genre"', false)
            ->assertDontSee('id="home-date"', false)
            ->assertDontSee('id="home-time"', false);
    }

    public function test_movies_page_does_not_show_the_popular_tab(): void
    {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertDontSee('Popular')
            ->assertSee(ui('all_movies', 'All Movies'))
            ->assertSee(ui('now_showing', 'Now Showing'))
            ->assertSee(ui('coming_soon', 'Coming Soon'));
    }

    public function test_homepage_shows_one_now_playing_movie_without_rating_or_trailer(): void
    {
        $featuredMovie = Movie::factory()->nowShowing()->create([
            'title' => 'Homepage Featured Film',
            'slug' => 'homepage-featured-film',
            'release_date' => '2026-10-10',
            'rating' => 7.9,
            'trailer_url' => 'https://www.youtube.com/embed/homepage-film',
            'is_featured' => false,
        ]);
        Movie::factory()->nowShowing()->create([
            'title' => 'Extra Homepage Film',
            'slug' => 'extra-homepage-film',
            'release_date' => '2026-10-09',
            'is_featured' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee($featuredMovie->title)
            ->assertDontSee('Extra Homepage Film')
            ->assertDontSee('data-trailer="https://www.youtube.com/embed/homepage-film"', false)
            ->assertDontSee('Watch Trailer')
            ->assertDontSee('7.9');
    }

    public function test_homepage_shows_all_six_features_in_english_and_nepali(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Everything you need for a smooth and enjoyable movie booking experience.')
            ->assertSee('Easy online booking')
            ->assertSee('Secure payment')
            ->assertSee('Instant digital tickets')
            ->assertSee('Convenient seat selection')
            ->assertSee('Multiple cinema branches')
            ->assertSee('English & Nepali support')
            ->assertSee('grid gap-4 sm:grid-cols-2 lg:grid-cols-3', false)
            ->assertSee('rounded-[20px]', false);

        $this->withSession(['locale' => 'np'])->get('/')
            ->assertOk()
            ->assertSee('चलचित्र टिकट बुकिङ सहज र रमाइलो बनाउन आवश्यक सबै कुरा।')
            ->assertSee('सजिलो अनलाइन बुकिङ')
            ->assertSee('सुरक्षित भुक्तानी')
            ->assertSee('तुरुन्तै डिजिटल टिकट')
            ->assertSee('सुविधाजनक सिट छनोट')
            ->assertSee('धेरै सिनेमा शाखाहरू')
            ->assertSee('अंग्रेजी र नेपाली सहयोग');
    }

    public function test_homepage_now_playing_omits_shikhar_and_maya_django(): void
    {
        Movie::factory()->nowShowing()->create([
            'title' => 'Shikhar',
            'slug' => 'shikhar',
        ]);
        Movie::factory()->nowShowing()->create([
            'title' => 'Maya Django',
            'slug' => 'maya-django',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Shikhar')
            ->assertDontSee('Maya Django');
    }

    public function test_movies_page_shows_booking_and_trailer_actions_for_coming_soon_movies(): void
    {
        $movie = Movie::factory()->comingSoon()->create([
            'title' => 'Coming Soon with Trailer',
            'trailer_url' => 'https://www.youtube.com/watch?v=coming-soon',
        ]);

        $this->get(route('movies.index'))
            ->assertOk()
            ->assertSee('href="'.route('movies.show', $movie).'#showtimes"', false)
            ->assertSee('data-trailer="https://www.youtube.com/watch?v=coming-soon"', false)
            ->assertSee('Book Tickets')
            ->assertSee('Watch Trailer');
    }

    public function test_movies_page_has_no_filter_bar(): void
    {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertDontSee('placeholder="Search movies"', false)
            ->assertDontSee('name="genre"', false)
            ->assertDontSee('type="date" name="show_date"', false)
            ->assertDontSee('type="time" name="show_time"', false);
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

    public function test_about_page_shows_kawasoti_location_without_a_map(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('img/about/cinema-screen.png')
            ->assertSee('img/about/cinema-hall.png')
            ->assertSee('What we stand for')
            ->assertDontSee('Cinema halls')
            ->assertDontSee('Tickets booked')
            ->assertDontSee('Cities across Nepal')
            ->assertSee('Today CineGo connects audiences with Kawasoti in English and Nepali, on any device. We are just getting started.')
            ->assertSee('Kawasoti-3, Nepal')
            ->assertDontSee('https://maps.google.com/maps?q=Kawasoti-3%2C%20Nepal', false)
            ->assertDontSee('title="Map showing Kawasoti-3, Nepal"', false)
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

    public function test_contact_page_shows_google_map_below_the_contact_panels(): void
    {
        $response = $this->get('/contact')->assertOk();
        $html = $response->getContent();
        $quickLinksPosition = strpos($html, ui('quick_links', 'Quick links'));
        $mapPosition = strpos($html, 'title="Map showing Kawasoti-3, Nepal"');

        $this->assertNotFalse($quickLinksPosition);
        $this->assertNotFalse($mapPosition);
        $this->assertGreaterThan($quickLinksPosition, $mapPosition);
        $response->assertSee('https://maps.google.com/maps?q=Kawasoti-3%2C%20Nepal&output=embed', false);
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
            ->assertSee('<summary data-showtime-option', false)
            ->assertSee('data-showtime-card', false)
            ->assertSee('min-w-[150px]', false)
            ->assertSee('grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4', false)
            ->assertDontSee($cinema->localised_name())
            ->assertDontSee($hall->name)
            ->assertSee(route('booking.seats', $showtime))
            ->assertSee('href="'.route('booking.seats', $showtime).'" class="btn-primary btn-block"', false)
            ->assertDontSee('data-booking-link', false)
            ->assertDontSee('>Dates</p>', false)
            ->assertDontSee('42 Hidden Street')
            ->assertDontSee('Hidden City');
    }

    public function test_chhaya_bazaar_demo_showtimes_are_available_for_booking(): void
    {
        $this->travelTo('2026-11-04 00:00:00');

        $movie = Movie::factory()->comingSoon()->create([
            'title' => 'Chhaya Bazaar',
            'slug' => 'chhaya-bazaar',
            'release_date' => '2026-12-02',
            'language' => 'Nepali',
        ]);

        $seeder = app(KawasotiDemoSeeder::class);
        $seeder->run();
        $seeder->run();

        $cinema = Cinema::query()->where('slug', 'cinego-kawasoti-demo')->firstOrFail();
        $hall = Hall::query()->where('cinema_id', $cinema->id)->where('name', 'Hall 1')->firstOrFail();
        $showtime = Showtime::query()->where('movie_id', $movie->id)->where('start_time', '10:30:00')->firstOrFail();

        $this->assertDatabaseCount('showtimes', 5);
        $this->assertDatabaseCount('seats', 112);
        $this->assertDatabaseHas('showtimes', [
            'id' => $showtime->id,
            'cinema_id' => $cinema->id,
            'hall_id' => $hall->id,
            'price_normal' => 450,
            'price_premium' => 650,
            'price_recliner' => 850,
        ]);
        $this->assertSame('2026-12-08', $showtime->show_date->toDateString());

        $this->get(route('movies.show', $movie))
            ->assertOk()
            ->assertSee('10:30 AM')
            ->assertSee('1:15 PM')
            ->assertSee('4:00 PM')
            ->assertSee('7:00 PM')
            ->assertSee('9:30 PM')
            ->assertSee('Rs. 450')
            ->assertSee(route('booking.seats', $showtime));

        $this->get(route('booking.seats', $showtime))
            ->assertOk()
            ->assertSee($movie->title)
            ->assertSee('aria-label="A1 — Available"', false);
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
            ->assertSee('<summary data-showtime-option', false)
            ->assertSee('href="'.route('booking.seats', $firstShowtime).'" class="btn-primary btn-block"', false)
            ->assertSee('href="'.route('booking.seats', $secondShowtime).'" class="btn-primary btn-block"', false)
            ->assertDontSee('href="'.route('booking.seats', $firstShowtime).'" class="group flex', false)
            ->assertDontSee('href="'.route('booking.seats', $secondShowtime).'" class="group flex', false);
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
