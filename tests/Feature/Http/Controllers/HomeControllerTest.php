<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_featured_movie_content_in_the_selected_locale(): void
    {
        Movie::query()->create([
            'title' => 'English title',
            'title_np' => 'नेपाली शीर्षक',
            'slug' => 'english-title',
            'synopsis' => 'English synopsis',
            'synopsis_np' => 'नेपाली कथासार',
            'genres' => ['Drama'],
            'duration_minutes' => 100,
            'release_date' => '2025-01-01',
            'poster_url' => '/img/poster.svg',
            'banner_url' => '/img/featured-banner.jpg',
            'is_now_showing' => true,
            'is_featured' => true,
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'np'])->get('/');

        $response->assertOk()
            ->assertSee('नेपाली शीर्षक')
            ->assertDontSee('English title')
            ->assertSee('नेपाली कथासार')
            ->assertSee('/img/featured-banner.jpg')
            ->assertSee('/movies/english-title')
            ->assertSee('सजिलो अनलाइन बुकिङ')
            ->assertSee('चलचित्र टिकट बुकिङ सहज र रमाइलो बनाउन आवश्यक सबै कुरा।');
    }

    public function test_homepage_uses_the_poster_when_a_featured_movie_has_no_banner(): void
    {
        Movie::factory()->featured()->create([
            'poster_url' => '/img/posters/fallback.svg',
            'banner_url' => null,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('/img/posters/fallback.svg', false);
    }

    public function test_homepage_uses_now_showing_movies_when_none_are_featured(): void
    {
        $movie = Movie::factory()->create([
            'title' => 'Now showing without a feature flag',
            'slug' => 'now-showing-without-feature',
            'is_now_showing' => true,
            'is_featured' => false,
            'is_active' => true,
            'banner_url' => '/img/banners/now-showing.svg',
        ]);
        Showtime::factory()->create([
            'movie_id' => $movie->id,
            'show_date' => now()->addDay()->toDateString(),
            'start_time' => '19:00:00',
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Now showing without a feature flag')
            ->assertSee('/img/banners/now-showing.svg', false);
    }

    public function test_homepage_does_not_treat_movies_without_showtimes_as_now_showing(): void
    {
        Movie::factory()->create([
            'title' => 'Flagged movie without a showtime',
            'slug' => 'flagged-movie-without-showtime',
            'is_now_showing' => true,
            'is_coming_soon' => false,
            'is_active' => true,
            'release_date' => now()->addMonth()->toDateString(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Coming Soon')
            ->assertSee('Next Releases')
            ->assertSee('Get ready for the movies arriving soon.')
            ->assertSee('Flagged movie without a showtime')
            ->assertViewHas('nowShowing', fn ($movies): bool => ! $movies->contains('slug', 'flagged-movie-without-showtime'))
            ->assertViewHas('comingSoon', fn ($movies): bool => $movies->contains('slug', 'flagged-movie-without-showtime'));
    }
}
