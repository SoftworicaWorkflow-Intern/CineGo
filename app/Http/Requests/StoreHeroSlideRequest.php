<?php

namespace App\Http\Requests;

use App\Models\HeroSlide;
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
        return [
            'movie_id' => ['nullable', 'integer', 'exists:movies,id'],
            'desktop_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=1920,min_height=1080'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=1080,min_height=1350'],
            'movie_title_en' => ['required', 'string', 'max:160'],
            'movie_title_np' => ['nullable', 'string', 'max:160'],
            'title_en' => ['required', 'string', 'max:160'],
            'title_np' => ['nullable', 'string', 'max:160'],
            'description_en' => ['required', 'string', 'max:1200'],
            'description_np' => ['nullable', 'string', 'max:1200'],
            'label_en' => ['nullable', 'string', 'max:60'],
            'label_np' => ['nullable', 'string', 'max:60'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:600'],
            'language' => ['nullable', 'string', 'max:40'],
            'release_date' => ['nullable', 'date'],
            'primary_button_text_en' => ['required', 'string', 'max:60'],
            'primary_button_text_np' => ['nullable', 'string', 'max:60'],
            'primary_button_url' => ['required', 'string', 'max:500', $this->safeDestination()],
            'secondary_button_text_en' => ['nullable', 'required_with:secondary_button_url', 'string', 'max:60'],
            'secondary_button_text_np' => ['nullable', 'string', 'max:60'],
            'secondary_button_url' => ['nullable', 'required_with:secondary_button_text_en', 'string', 'max:500', $this->safeDestination()],
            'display_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'status' => ['required', Rule::in([HeroSlide::STATUS_DRAFT, HeroSlide::STATUS_PUBLISHED])],
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

                if (strtotime($this->string('ends_at')->toString()) < strtotime($this->string('starts_at')->toString())) {
                    $validator->errors()->add('ends_at', 'The publish end must be after the publish start.');
                }
            },
        ];
    }

    /**
     * @return \Closure(string, mixed, \Closure(string): void): void
     */
    protected function safeDestination(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_string($value) || $value === '') {
                $fail('The destination must be a local path or an HTTP(S) URL.');

                return;
            }

            $scheme = parse_url($value, PHP_URL_SCHEME);
            $path = parse_url($value, PHP_URL_PATH);
            $decodedPath = is_string($path) ? rawurldecode($path) : '';
            $isLocalPath = str_starts_with($value, '/')
                && ! str_starts_with($value, '//')
                && ! str_contains($value, '\\')
                && ! preg_match('/[\x00-\x20]/', $value)
                && ! preg_match('~(?:^|/)\.\.(?:/|$)~', $decodedPath);
            $isWebUrl = in_array(strtolower((string) $scheme), ['http', 'https'], true)
                && parse_url($value, PHP_URL_HOST) !== null
                && parse_url($value, PHP_URL_USER) === null
                && filter_var($value, FILTER_VALIDATE_URL) !== false;

            if (! $isLocalPath && ! $isWebUrl) {
                $fail('The destination must be a local path or an HTTP(S) URL.');
            }
        };
    }

    public function messages(): array
    {
        return [
            'desktop_image.required' => 'Please upload a desktop hero image.',
            'desktop_image.dimensions' => 'The desktop image must be at least 1920 × 1080 pixels.',
            'mobile_image.dimensions' => 'The mobile image must be at least 1080 × 1350 pixels.',
            'ends_at.after_or_equal' => 'The publish end must be after the publish start.',
        ];
    }
}
