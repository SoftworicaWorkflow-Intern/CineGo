<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMovieIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_movie_serial_numbers_continue_across_paginated_results(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Movie::factory()->count(16)->create();

        $this->actingAs($admin)
            ->get(route('admin.movies.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('data-serial-number="16"', false);
    }
}
