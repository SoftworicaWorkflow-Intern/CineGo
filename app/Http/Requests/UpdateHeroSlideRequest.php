<?php

namespace App\Http\Requests;

class UpdateHeroSlideRequest extends StoreHeroSlideRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('heroSlide')) ?? false;
    }

    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'desktop_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=1920,min_height=1080'],
            'remove_desktop_image' => ['sometimes', 'boolean'],
            'remove_mobile_image' => ['sometimes', 'boolean'],
        ]);
    }

    public function messages(): array
    {
        return parent::messages();
    }
}
