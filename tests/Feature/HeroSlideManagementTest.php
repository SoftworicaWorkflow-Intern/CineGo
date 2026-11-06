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

    public function test_admin_can_create_update_preview_publish_and_delete_a_slide(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $attributes = $this->slideAttributes();

        $response = $this->actingAs($admin)->post(route('admin.hero-sliders.store'), [
            ...$attributes,
            'desktop_image' => $this->pngFile('desktop.png', 1920, 1080),
            'mobile_image' => $this->pngFile('mobile.png', 1080, 1350),
        ]);

        $slide = HeroSlide::query()->firstOrFail();
        $response->assertRedirect(route('admin.hero-sliders.edit', $slide));
        $this->assertModelExists($slide);
        $this->assertDatabaseHas('hero_slides', [
            'id' => $slide->id,
            'title_en' => 'The premiere',
            'status' => 'draft',
        ]);
        Storage::disk('public')->assertExists([$slide->desktop_image, $slide->mobile_image]);

        $this->actingAs($admin)->get(route('admin.hero-sliders.preview', $slide))->assertOk()->assertSee('The premiere');

        $oldDesktopImage = $slide->desktop_image;
        $oldMobileImage = $slide->mobile_image;
        $this->actingAs($admin)->put(route('admin.hero-sliders.update', $slide), [
            ...$this->slideAttributes([
                'title_en' => 'A new premiere',
                'status' => 'published',
            ]),
            'desktop_image' => $this->pngFile('replacement.png', 1920, 1080),
            'remove_mobile_image' => '1',
        ])->assertRedirect(route('admin.hero-sliders.edit', $slide));

        $slide->refresh();
        $this->assertSame('A new premiere', $slide->title_en);
        $this->assertSame('published', $slide->status);
        $this->assertNull($slide->mobile_image);
        Storage::disk('public')->assertMissing([$oldDesktopImage, $oldMobileImage]);
        Storage::disk('public')->assertExists($slide->desktop_image);

        $replacementImage = $slide->desktop_image;
        $removeDesktopData = $this->slideAttributes(['status' => 'published']);
        unset($removeDesktopData['desktop_image']);
        $this->actingAs($admin)->put(route('admin.hero-sliders.update', $slide), [
            ...$removeDesktopData,
            'remove_desktop_image' => '1',
        ])->assertRedirect(route('admin.hero-sliders.edit', $slide));

        $slide->refresh();
        $this->assertNull($slide->desktop_image);
        Storage::disk('public')->assertMissing($replacementImage);

        $this->actingAs($admin)->delete(route('admin.hero-sliders.destroy', $slide))
            ->assertRedirect(route('admin.hero-sliders.index'));

        $this->assertModelMissing($slide);
        Storage::disk('public')->assertMissing($slide->desktop_image);
    }

    public function test_guests_and_customers_cannot_manage_hero_slides(): void
    {
        $this->get(route('admin.hero-sliders.index'))->assertRedirect(route('login'));

        $customer = User::factory()->create();
        $this->actingAs($customer)->get(route('admin.hero-sliders.index'))->assertForbidden();
    }

    public function test_admin_can_load_the_slide_dashboard_and_live_preview_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.hero-sliders.index'))
            ->assertOk()
            ->assertSee('Homepage slides')
            ->assertSeeInOrder(['Showtime', 'Hero Banner'])
            ->assertDontSee('Homepage Banners');

        $this->actingAs($admin)->get(route('admin.hero-sliders.create'))
            ->assertOk()
            ->assertSee('Desktop hero image')
            ->assertSee('Live preview')
            ->assertSee('नेपाली');
    }

    public function test_reorder_saves_the_order_only_when_all_slides_are_included(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $first = HeroSlide::query()->create($this->slideAttributes(['display_order' => 0]));
        $second = HeroSlide::query()->create($this->slideAttributes(['display_order' => 1, 'title_en' => 'Second slide']));

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

        $this->assertSame(0, $second->fresh()->display_order);
        $this->assertSame(1, $first->fresh()->display_order);
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

    public function test_homepage_renders_only_published_slides_inside_their_schedule_in_order(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 6)->setTime(12, 0));
        HeroSlide::query()->create($this->slideAttributes([
            'title_en' => 'First heading',
            'display_order' => 1,
            'status' => 'published',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]));
        HeroSlide::query()->create($this->slideAttributes([
            'title_en' => 'Second heading',
            'display_order' => 2,
            'status' => 'published',
        ]));
        HeroSlide::query()->create($this->slideAttributes([
            'title_en' => 'Future heading',
            'display_order' => 0,
            'status' => 'published',
            'starts_at' => now()->addDay(),
        ]));
        HeroSlide::query()->create($this->slideAttributes([
            'title_en' => 'Expired heading',
            'display_order' => 3,
            'status' => 'published',
            'ends_at' => now()->subDay(),
        ]));
        HeroSlide::query()->create($this->slideAttributes([
            'title_en' => 'Draft heading',
            'display_order' => 4,
            'status' => 'draft',
        ]));

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSeeInOrder(['data-hero-title="First heading"', 'data-hero-title="Second heading"'])
            ->assertDontSee('Future heading')
            ->assertDontSee('Expired heading')
            ->assertDontSee('Draft heading')
            ->assertSee('/movies/premiere');
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
            ->assertDontSee('data-hero-title="Legacy featured movie"', false);
    }

    public function test_invalid_image_dimensions_and_unsafe_button_urls_are_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->from(route('admin.hero-sliders.create'))
            ->post(route('admin.hero-sliders.store'), [
                ...$this->slideAttributes(['primary_button_url' => 'javascript:alert(1)']),
                'desktop_image' => $this->pngFile('small.png', 100, 50),
            ])
            ->assertSessionHasErrors([
                'desktop_image' => 'The desktop image must be at least 1920 × 1080 pixels.',
                'primary_button_url' => 'The destination must be a local path or an HTTP(S) URL.',
            ]);

        $this->assertDatabaseCount('hero_slides', 0);
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

    public function test_homepage_escapes_administrator_supplied_slide_content(): void
    {
        HeroSlide::query()->create($this->slideAttributes([
            'title_en' => '<script>alert(1)</script>',
            'status' => 'published',
        ]));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function slideAttributes(array $overrides = []): array
    {
        return array_merge([
            'desktop_image' => 'img/banners/banner-1.svg',
            'movie_title_en' => 'Premiere',
            'movie_title_np' => null,
            'title_en' => 'The premiere',
            'title_np' => null,
            'description_en' => 'A new story arrives in cinemas.',
            'description_np' => null,
            'label_en' => 'NOW SHOWING',
            'label_np' => null,
            'duration' => 110,
            'language' => 'Nepali',
            'release_date' => '2026-11-06',
            'primary_button_text_en' => 'Book Tickets',
            'primary_button_text_np' => null,
            'primary_button_url' => '/movies/premiere',
            'secondary_button_text_en' => 'Explore Movies',
            'secondary_button_text_np' => null,
            'secondary_button_url' => '/movies',
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
