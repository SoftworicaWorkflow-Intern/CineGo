<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Movie;
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
}
