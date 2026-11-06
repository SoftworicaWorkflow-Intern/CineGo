<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroSlideManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_preview_publish_and_delete_a_movie_banner(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $movie = Movie::factory()->comingSoon()->create(['title' => 'Linked movie', 'slug' => 'linked-movie']);
        $attributes = $this->slideAttributes(['movie_id' => $movie->id]);
        unset($attributes['status'], $attributes['display_order']);

        $response = $this->actingAs($admin)->post(route('admin.hero-sliders.store'), [
            ...$attributes,
            'desktop_image' => $this->pngFile('desktop.png', 1920, 1080),
            'mobile_image' => $this->pngFile('mobile.png', 600, 900),
        ]);

        $slide = HeroSlide::query()->whereBelongsTo($movie)->firstOrFail();
        $response->assertRedirect(route('admin.hero-sliders.edit', $slide));
        $this->assertModelExists($slide);
        $this->assertDatabaseHas('hero_slides', [
            'id' => $slide->id,
            'movie_id' => $movie->id,
            'internal_title' => null,
            'status' => 'published',
        ]);
        Storage::disk('public')->assertExists($slide->desktop_image);
        Storage::disk('public')->assertExists($slide->mobile_image);

        $this->actingAs($admin)->get(route('admin.hero-sliders.preview', $slide))
            ->assertOk()
            ->assertSee('Linked movie')
            ->assertSee(route('movies.show', $movie));

        $oldDesktopImage = $slide->desktop_image;
        $oldMobileImage = $slide->mobile_image;
        $replacementMovie = Movie::factory()->comingSoon()->create(['title' => 'Replacement movie']);
        $updateData = $this->slideAttributes([
            'movie_id' => $replacementMovie->id,
        ]);
        unset(
            $updateData['internal_title'],
            $updateData['display_order'],
            $updateData['status'],
            $updateData['starts_at'],
            $updateData['ends_at'],
        );
        $this->actingAs($admin)->put(route('admin.hero-sliders.update', $slide), [
            ...$updateData,
            'desktop_image' => $this->pngFile('replacement.png', 1920, 1080),
            'mobile_image' => $this->pngFile('replacement-mobile.png', 600, 900),
        ])->assertRedirect(route('admin.hero-sliders.edit', $slide));

        $slide->refresh();
        $this->assertSame($replacementMovie->id, $slide->movie_id);
        $this->assertNull($slide->internal_title);
        $this->assertSame('published', $slide->status);
        Storage::disk('public')->assertMissing($oldDesktopImage);
        Storage::disk('public')->assertMissing($oldMobileImage);
        Storage::disk('public')->assertExists($slide->desktop_image);
        Storage::disk('public')->assertExists($slide->mobile_image);

        $replacementImage = $slide->desktop_image;
        $replacementMobileImage = $slide->mobile_image;
        $removeImageData = $this->slideAttributes([
            'movie_id' => $replacementMovie->id,
            'internal_title' => 'Updated homepage feature',
        ]);
        unset(
            $removeImageData['desktop_image'],
            $removeImageData['internal_title'],
            $removeImageData['display_order'],
            $removeImageData['status'],
            $removeImageData['starts_at'],
            $removeImageData['ends_at'],
        );
        $this->actingAs($admin)->put(route('admin.hero-sliders.update', $slide), [
            ...$removeImageData,
            'remove_desktop_image' => '1',
        ])->assertRedirect(route('admin.hero-sliders.edit', $slide));

        $slide->refresh();
        $this->assertNull($slide->desktop_image);
        $this->assertSame($replacementMobileImage, $slide->mobile_image);
        Storage::disk('public')->assertMissing($replacementImage);

        $mobileRemovalData = $this->slideAttributes([
            'movie_id' => $replacementMovie->id,
            'internal_title' => 'Updated homepage feature',
        ]);
        unset(
            $mobileRemovalData['desktop_image'],
            $mobileRemovalData['internal_title'],
            $mobileRemovalData['display_order'],
            $mobileRemovalData['status'],
            $mobileRemovalData['starts_at'],
            $mobileRemovalData['ends_at'],
        );
        $this->actingAs($admin)->put(route('admin.hero-sliders.update', $slide), [
            ...$mobileRemovalData,
            'remove_mobile_image' => '1',
        ])->assertRedirect(route('admin.hero-sliders.edit', $slide));

        $slide->refresh();
        $this->assertNull($slide->mobile_image);
        Storage::disk('public')->assertMissing($replacementMobileImage);

        $this->actingAs($admin)->delete(route('admin.hero-sliders.destroy', $slide))
            ->assertRedirect(route('admin.hero-sliders.index'));

        $this->assertModelMissing($slide);
    }

    public function test_guests_and_customers_cannot_manage_hero_slides(): void
    {
        $this->get(route('admin.hero-sliders.index'))->assertRedirect(route('login'));

        $customer = User::factory()->create();
        $this->actingAs($customer)->get(route('admin.hero-sliders.index'))->assertForbidden();
    }

    public function test_admin_sees_existing_movies_and_movie_backed_banner_preview(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $movie = Movie::factory()->create(['title' => 'Available movie']);
        $slide = HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $movie->id,
            'mobile_image' => 'hero-slides/legacy-mobile.jpg',
            'ends_at' => now()->addDay(),
        ]));
        $existingEndDate = $slide->getRawOriginal('ends_at');

        $this->actingAs($admin)->get(route('admin.hero-sliders.index'))
            ->assertOk()
            ->assertSee('Homepage banners')
            ->assertSee('Available movie')
            ->assertSeeInOrder(['Showtime', 'Hero Banner'])
            ->assertDontSee('Homepage Banners');

        $this->actingAs($admin)->get(route('admin.hero-sliders.edit', $slide))
            ->assertOk()
            ->assertSee('Select Movie')
            ->assertSee('Available movie')
            ->assertSee('Choose a coming soon movie')
            ->assertSee('Live banner preview')
            ->assertSee('data-movie-select', false)
            ->assertDontSee('data-preview-poster', false)
            ->assertDontSee('data-poster=', false)
            ->assertDontSee('internal_title')
            ->assertDontSee('Mobile banner image')
            ->assertDontSee('Display settings')
            ->assertDontSee('name="mobile_image"', false)
            ->assertDontSee('name="display_order"', false)
            ->assertDontSee('name="status"', false)
            ->assertDontSee('name="starts_at"', false)
            ->assertDontSee('name="ends_at"', false)
            ->assertDontSee('name="title_en"', false)
            ->assertDontSee('name="description_en"', false)
            ->assertDontSee('name="primary_button_url"', false)
            ->assertDontSee('name="movie_title_en"', false);

        $updateData = $this->slideAttributes([
            'movie_id' => $movie->id,
        ]);
        unset(
            $updateData['desktop_image'],
            $updateData['internal_title'],
            $updateData['mobile_image'],
            $updateData['display_order'],
            $updateData['status'],
            $updateData['starts_at'],
            $updateData['ends_at'],
        );
        $this->actingAs($admin)->put(route('admin.hero-sliders.update', $slide), [
            ...$updateData,
        ])->assertRedirect(route('admin.hero-sliders.edit', $slide));

        $this->assertDatabaseHas('hero_slides', [
            'id' => $slide->id,
            'movie_id' => $movie->id,
            'internal_title' => 'Homepage feature',
            'mobile_image' => 'hero-slides/legacy-mobile.jpg',
            'ends_at' => $existingEndDate,
        ]);
    }

    public function test_admin_can_create_a_banner_from_an_existing_movie_without_duplicate_movie_fields(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $existingMovie = Movie::factory()->create(['title' => 'Existing homepage movie']);
        $existingSlide = HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $existingMovie->id,
            'desktop_image' => 'img/banners/existing.svg',
            'status' => 'published',
        ]));
        $movie = Movie::factory()->comingSoon()->create(['title' => 'Existing catalogue movie']);

        $response = $this->actingAs($admin)->post(route('admin.hero-sliders.store'), [
            'movie_id' => $movie->id,
            'desktop_image' => $this->pngFile('banner.png', 1920, 1080),
        ]);

        $slide = HeroSlide::query()->whereBelongsTo($movie)->firstOrFail();
        $response->assertRedirect(route('admin.hero-sliders.edit', $slide));
        $this->assertDatabaseHas('hero_slides', [
            'id' => $slide->id,
            'movie_id' => $movie->id,
            'internal_title' => null,
            'status' => 'published',
            'display_order' => 0,
            'starts_at' => null,
            'ends_at' => null,
        ]);
        $this->assertDatabaseHas('hero_slides', [
            'id' => $existingSlide->id,
            'display_order' => 1,
        ]);
        $this->assertSame($movie->id, $slide->movie->id);
        Storage::disk('public')->assertExists($slide->desktop_image);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-hero-page', false)
            ->assertSeeInOrder([
                'data-hero-image="'.Storage::disk('public')->url($slide->desktop_image).'"',
                'data-hero-image="'.asset('img/banners/existing.svg').'"',
            ], false)
            ->assertSee('Existing catalogue movie')
            ->assertSee(route('movies.show', $movie));
    }

    public function test_movie_can_only_be_used_by_one_banner(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $movie = Movie::factory()->comingSoon()->create();
        HeroSlide::query()->create($this->slideAttributes(['movie_id' => $movie->id]));

        $this->actingAs($admin)->from(route('admin.hero-sliders.create'))
            ->post(route('admin.hero-sliders.store'), [
                ...$this->slideAttributes(['movie_id' => $movie->id]),
                'desktop_image' => $this->pngFile('duplicate.png', 1920, 1080),
            ])
            ->assertSessionHasErrors('movie_id');

        $this->assertDatabaseCount('hero_slides', 1);
    }

    public function test_reorder_saves_the_order_only_when_all_slides_are_included(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $first = HeroSlide::query()->create($this->slideAttributes(['display_order' => 0]));
        $second = HeroSlide::query()->create($this->slideAttributes(['display_order' => 1]));

        $this->actingAs($admin)->postJson(route('admin.hero-sliders.reorder'), [
            'slide_ids' => [$second->id, $first->id],
        ])->assertOk()->assertJson(['message' => 'Slide order saved.']);

        $this->assertSame(0, $second->fresh()->display_order);
        $this->assertSame(1, $first->fresh()->display_order);

        $this->actingAs($admin)->postJson(route('admin.hero-sliders.reorder'), [
            'slide_ids' => [$first->id],
        ])->assertUnprocessable()->assertJson([
            'message' => 'The slide list changed. Reload the page and try again.',
        ]);
    }

    public function test_admin_can_publish_and_unpublish_a_slide(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $slide = HeroSlide::query()->create($this->slideAttributes());

        $this->actingAs($admin)->patch(route('admin.hero-sliders.status', $slide))
            ->assertRedirect();
        $this->assertDatabaseHas('hero_slides', ['id' => $slide->id, 'status' => 'published']);

        $this->actingAs($admin)->patch(route('admin.hero-sliders.status', $slide))
            ->assertRedirect();
        $this->assertDatabaseHas('hero_slides', ['id' => $slide->id, 'status' => 'draft']);
    }

    public function test_homepage_renders_scheduled_slides_in_order_with_current_movie_details_and_actions(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 6)->setTime(12, 0));
        $firstMovie = Movie::factory()->create([
            'title' => 'Current movie title',
            'synopsis' => 'Current movie synopsis',
            'genres' => ['Drama', 'Mystery'],
            'duration_minutes' => 110,
            'language' => 'Nepali',
            'release_date' => '2026-11-06',
            'certificate' => 'U/A',
            'trailer_url' => 'https://www.youtube.com/watch?v=abc123',
            'slug' => 'current-movie',
        ]);
        $secondMovie = Movie::factory()->create([
            'title' => 'Second movie title',
            'slug' => 'second-movie',
            'trailer_url' => null,
        ]);
        HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $firstMovie->id,
            'desktop_image' => 'img/banners/first.svg',
            'display_order' => 1,
            'status' => 'published',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]));
        HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $secondMovie->id,
            'desktop_image' => 'img/banners/second.svg',
            'display_order' => 2,
            'status' => 'published',
        ]));
        $futureMovie = Movie::factory()->create(['title' => 'Future movie title']);
        HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $futureMovie->id,
            'display_order' => 0,
            'status' => 'published',
            'starts_at' => now()->addDay(),
        ]));
        $expiredMovie = Movie::factory()->create(['title' => 'Expired movie title']);
        HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $expiredMovie->id,
            'display_order' => 3,
            'status' => 'published',
            'ends_at' => now()->subDay(),
        ]));
        $draftMovie = Movie::factory()->create(['title' => 'Draft movie title']);
        HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $draftMovie->id,
            'display_order' => 4,
            'status' => 'draft',
        ]));

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'data-hero-title="Current movie title"',
                'data-hero-title="Second movie title"',
            ], false)
            ->assertSee('Current movie synopsis')
            ->assertSee('Drama, Mystery')
            ->assertSee('1h 50m')
            ->assertSee('Nepali')
            ->assertSee('U/A')
            ->assertSee('Nov 6, 2026')
            ->assertSee('data-hero-trailer-url="https://www.youtube.com/watch?v=abc123"', false)
            ->assertSee('data-hero-trailer-url=""', false)
            ->assertSee(route('movies.show', $firstMovie))
            ->assertDontSee('data-hero-title="Future movie title"', false)
            ->assertDontSee('data-hero-title="Expired movie title"', false)
            ->assertDontSee('data-hero-title="Draft movie title"', false);
    }

    public function test_movie_updates_are_reflected_in_the_homepage_banner_without_updating_the_banner(): void
    {
        $movie = Movie::factory()->create(['title' => 'Original title']);
        $slide = HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $movie->id,
            'status' => 'published',
        ]));

        $movie->update([
            'title' => 'Latest title from Movies',
            'synopsis' => 'Latest synopsis from Movies',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Latest title from Movies')
            ->assertSee('Latest synopsis from Movies')
            ->assertDontSee('Original title');

        $this->assertSame($movie->id, $slide->fresh()->movie_id);
    }

    public function test_homepage_uses_a_mobile_banner_on_small_screens_and_falls_back_to_desktop_image(): void
    {
        $mobileMovie = Movie::factory()->create(['slug' => 'mobile-banner-movie']);
        HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $mobileMovie->id,
            'desktop_image' => 'img/banners/desktop.svg',
            'mobile_image' => 'hero-slides/mobile.webp',
            'status' => 'published',
        ]));

        $fallbackMovie = Movie::factory()->create(['slug' => 'fallback-banner-movie']);
        HeroSlide::query()->create($this->slideAttributes([
            'movie_id' => $fallbackMovie->id,
            'desktop_image' => 'img/banners/fallback.svg',
            'status' => 'published',
            'display_order' => 1,
        ]));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(
                'data-hero-mobile-image="'.Storage::disk('public')->url('hero-slides/mobile.webp').'"',
                false
            )
            ->assertSee(
                'data-hero-mobile-image="'.asset('img/banners/fallback.svg').'"',
                false
            );
    }

    public function test_unpublished_managed_slides_do_not_fall_back_to_featured_movies(): void
    {
        Movie::factory()->featured()->create([
            'title' => 'Legacy featured movie',
            'slug' => 'legacy-featured-movie',
        ]);
        HeroSlide::query()->create($this->slideAttributes(['status' => 'draft']));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-hero-page', false);
    }

    public function test_desktop_banner_below_the_former_minimum_dimensions_is_accepted(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $movie = Movie::factory()->comingSoon()->create();

        $response = $this->actingAs($admin)->post(route('admin.hero-sliders.store'), [
            ...$this->slideAttributes(['movie_id' => $movie->id]),
            'desktop_image' => $this->pngFile('desktop-banner.png', 640, 100),
        ]);

        $slide = HeroSlide::query()->firstOrFail();
        $response->assertRedirect(route('admin.hero-sliders.edit', $slide));
        Storage::disk('public')->assertExists($slide->desktop_image);
    }

    public function test_publish_end_cannot_be_earlier_than_the_start(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->from(route('admin.hero-sliders.create'))
            ->post(route('admin.hero-sliders.store'), [
                ...$this->slideAttributes([
                    'starts_at' => '2026-11-07T12:00',
                    'ends_at' => '2026-11-06T12:00',
                ]),
                'desktop_image' => $this->pngFile('desktop.png', 1920, 1080),
            ])
            ->assertSessionHasErrors([
                'ends_at' => 'The publish end must be after the publish start.',
            ]);

        $this->assertDatabaseCount('hero_slides', 0);
    }

    public function test_admin_movie_selector_lists_only_available_upcoming_movies(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $upcomingMovie = Movie::factory()->comingSoon()->create();
        $nowShowingMovie = Movie::factory()->nowShowing()->create();
        $inactiveUpcomingMovie = Movie::factory()->comingSoon()->create(['is_active' => false]);
        $alreadyLinkedMovie = Movie::factory()->comingSoon()->create();
        HeroSlide::query()->create($this->slideAttributes(['movie_id' => $alreadyLinkedMovie->id]));

        $this->actingAs($admin)->get(route('admin.hero-sliders.create'))
            ->assertOk()
            ->assertSee('value="'.$upcomingMovie->id.'"', false)
            ->assertDontSee('value="'.$nowShowingMovie->id.'"', false)
            ->assertDontSee('value="'.$inactiveUpcomingMovie->id.'"', false)
            ->assertDontSee('value="'.$alreadyLinkedMovie->id.'"', false);
    }

    public function test_admin_cannot_create_a_banner_for_a_non_upcoming_movie(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $nowShowingMovie = Movie::factory()->nowShowing()->create();

        $this->actingAs($admin)->from(route('admin.hero-sliders.create'))
            ->post(route('admin.hero-sliders.store'), [
                'movie_id' => $nowShowingMovie->id,
                'desktop_image' => $this->pngFile('now-showing.png', 1920, 1080),
            ])
            ->assertSessionHasErrors('movie_id');

        $this->assertDatabaseCount('hero_slides', 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function slideAttributes(array $overrides = []): array
    {
        return array_merge([
            'movie_id' => Movie::factory()->comingSoon()->create()->id,
            'desktop_image' => 'img/banners/banner-1.svg',
            'mobile_image' => null,
            'internal_title' => 'Homepage feature',
            'display_order' => 0,
            'status' => 'draft',
            'starts_at' => null,
            'ends_at' => null,
        ], $overrides);
    }

    private function pngFile(string $name, int $width, int $height): UploadedFile
    {
        $pngChunk = static fn (string $type, string $contents): string => pack('N', strlen($contents))
            .$type
            .$contents
            .pack('H*', hash('crc32b', $type.$contents));
        $imageData = str_repeat("\0".str_repeat("\0", $width), $height);
        $header = pack('NNCCCCC', $width, $height, 8, 0, 0, 0, 0);
        $contents = "\x89PNG\r\n\x1a\n"
            .$pngChunk('IHDR', $header)
            .$pngChunk('IDAT', gzcompress($imageData))
            .$pngChunk('IEND', '');

        return UploadedFile::fake()->createWithContent($name, $contents);
    }
}
