<?php

namespace Tests\Feature;

use App\Models\User;
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
}
