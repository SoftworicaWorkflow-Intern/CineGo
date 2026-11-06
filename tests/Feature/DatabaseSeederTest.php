<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\City;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_preserves_an_existing_admin_account(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@cinego.com.np',
            'password' => Hash::make('existing-password'),
        ]);
        $admin->forceFill(['is_admin' => true])->save();

        $this->seed();

        $this->assertDatabaseCount('users', 3);
        $this->assertTrue(Hash::check('existing-password', $admin->fresh()->password));
    }

    public function test_seeding_creates_the_admin_account_with_admin_access(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@cinego.com.np',
            'is_admin' => true,
        ]);
    }

    public function test_seeding_preserves_an_existing_city_without_creating_a_duplicate(): void
    {
        $city = City::query()->create([
            'name' => 'Existing Kathmandu',
            'name_np' => 'काठमाडौं',
            'slug' => 'kathmandu',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $this->seed();

        $this->assertDatabaseCount('cities', 7);
        $this->assertSame('Existing Kathmandu', $city->fresh()->name);
    }

    public function test_seeding_skips_demo_records_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->app->make(DatabaseSeeder::class)->run();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('cities', 0);
    }

    public function test_seeding_includes_kawasoti_demo_showtimes(): void
    {
        $this->seed();

        $movie = Movie::query()->where('slug', 'chhaya-bazaar')->firstOrFail();
        $cinema = Cinema::query()->where('slug', 'cinego-kawasoti-demo')->firstOrFail();

        $this->assertSame(5, Showtime::query()
            ->where('movie_id', $movie->id)
            ->where('cinema_id', $cinema->id)
            ->count());
    }
}
