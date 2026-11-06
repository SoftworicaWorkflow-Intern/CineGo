<?php

namespace App\Http\Requests;

use App\Models\HeroSlide;
use Illuminate\Foundation\Http\FormRequest;

class ReorderHeroSlidesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reorder', HeroSlide::class) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'slide_ids' => ['required', 'array', 'min:1'],
            'slide_ids.*' => ['required', 'integer', 'distinct', 'exists:hero_slides,id'],
        ];
    }
}
