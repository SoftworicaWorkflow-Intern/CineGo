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

if (! function_exists('is_current_city')) {
    function is_current_city(int $cityId): bool
    {
        return (int) session('city_id', 0) === $cityId;
    }
}
