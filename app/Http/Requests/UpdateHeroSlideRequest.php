<?php

namespace App\Http\Requests;

use App\Models\HeroSlide;

class UpdateHeroSlideRequest extends StoreHeroSlideRequest
{
    public function authorize(): bool
    {
        $heroSlide = $this->route('heroSlide');

        return $heroSlide instanceof HeroSlide && ($this->user()?->can('update', $heroSlide) ?? false);
    }

    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'desktop_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'remove_desktop_image' => ['sometimes', 'boolean'],
            'remove_mobile_image' => ['sometimes', 'boolean'],
        ]);
    }

    public function messages(): array
    {
        return parent::messages();
    }
}
