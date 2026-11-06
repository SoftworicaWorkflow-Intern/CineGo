<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class HeroSlidePolicyTest extends TestCase
{
    public function test_only_administrators_can_manage_hero_slides(): void
    {
        $admin = User::factory()->make(['is_admin' => true]);
        $customer = User::factory()->make(['is_admin' => false]);
        $slide = new HeroSlide;
        $abilities = [
            'viewAny' => [HeroSlide::class],
            'view' => [$slide],
            'create' => [HeroSlide::class],
            'update' => [$slide],
            'delete' => [$slide],
            'reorder' => [HeroSlide::class],
        ];

        foreach ($abilities as $ability => $arguments) {
            $this->assertSame(true, Gate::forUser($admin)->allows($ability, $arguments));
            $this->assertSame(false, Gate::forUser($customer)->allows($ability, $arguments));
        }
    }
}
