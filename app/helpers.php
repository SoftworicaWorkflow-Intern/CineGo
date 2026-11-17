<?php

use App\Models\City;

if (! function_exists('ui')) {
    /**
     * Translate a UI string, falling back to the given English text when the
     * key has not been translated yet.
     *
     * @param  array<string, string|int|float>  $replace
     */
    function ui(string $key, string $fallback, array $replace = []): string
    {
        $line = __("ui.{$key}", $replace);

        if (! is_string($line) || $line === "ui.{$key}") {
            return $fallback;
        }

        return $line;
    }
}

if (! function_exists('selected_city')) {
    /**
     * The city the visitor has picked in the header selector (null = all cities).
     */
    function selected_city(): ?City
    {
        $cityId = session('city_id');

        if (! $cityId) {
            return null;
        }

        return City::query()->active()->find($cityId);
    }
}

if (! function_exists('public_media_url')) {
    /**
     * Turn a stored poster or banner into a URL for the current site.
     * Uploads saved while APP_URL was localhost stay usable in production.
     */
    function public_media_url(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $path = parse_url($value, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return $value;
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, '/img/')) {
            return $path;
        }

        return $value;
    }
}

if (! function_exists('is_current_city')) {
    function is_current_city(int $cityId): bool
    {
        return (int) session('city_id', 0) === $cityId;
    }
}
