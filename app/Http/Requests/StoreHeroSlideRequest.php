<?php

namespace App\Http\Requests;

use App\Models\HeroSlide;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHeroSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', HeroSlide::class) ?? false;
    }

    public function rules(): array
    {
        $movieUniqueRule = Rule::unique('hero_slides', 'movie_id');
        $heroSlide = $this->route('heroSlide');
        $availableMovieRule = Rule::exists('movies', 'id')
            ->where(function (QueryBuilder $query) use ($heroSlide): void {
                $query->where(function (QueryBuilder $query): void {
                    $query->where('is_active', true)
                        ->where('is_coming_soon', true);
                });

                if ($heroSlide instanceof HeroSlide) {
                    $query->orWhere('id', $heroSlide->movie_id);
                }
            });

        if ($heroSlide instanceof HeroSlide) {
            $movieUniqueRule->ignore($heroSlide->getKey());
        }

        return [
            'movie_id' => ['required', 'integer', $availableMovieRule, $movieUniqueRule],
            'desktop_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'display_order' => ['sometimes', 'integer', 'min:0', 'max:4294967295'],
            'status' => ['sometimes', Rule::in([HeroSlide::STATUS_DRAFT, HeroSlide::STATUS_PUBLISHED])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->filled('starts_at') || ! $this->filled('ends_at')
                    || $validator->errors()->hasAny(['starts_at', 'ends_at'])) {
                    return;
                }

                if (strtotime($this->string('ends_at')->toString()) <= strtotime($this->string('starts_at')->toString())) {
                    $validator->errors()->add('ends_at', 'The publish end must be after the publish start.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'movie_id.required' => 'Please select a movie for this banner.',
            'movie_id.unique' => 'This movie already has a hero banner.',
            'desktop_image.required' => 'Please upload a desktop hero image.',
        ];
    }
}
