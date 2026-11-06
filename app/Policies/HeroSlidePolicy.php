<?php

namespace App\Policies;

use App\Models\HeroSlide;
use App\Models\User;

class HeroSlidePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, HeroSlide $heroSlide): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, HeroSlide $heroSlide): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, HeroSlide $heroSlide): bool
    {
        return $user->is_admin;
    }

    public function reorder(User $user): bool
    {
        return $user->is_admin;
    }
}
