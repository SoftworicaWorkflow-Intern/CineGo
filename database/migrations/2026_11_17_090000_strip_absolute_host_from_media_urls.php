<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Poster URLs were saved with APP_URL, including http://localhost:8000.
     * Keep only the site path so the browser loads them from the current host.
     */
    public function up(): void
    {
        $columns = [
            'movies' => ['poster_url', 'banner_url'],
            'hero_slides' => ['desktop_image', 'mobile_image'],
        ];

        foreach ($columns as $table => $names) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($names as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->select(['id', $column])
                    ->whereNotNull($column)
                    ->orderBy('id')
                    ->each(function (object $row) use ($table, $column): void {
                        $path = self::sitePath($row->{$column});

                        if ($path === null || $path === $row->{$column}) {
                            return;
                        }

                        DB::table($table)->where('id', $row->id)->update([
                            $column => $path,
                        ]);
                    });
            }
        }
    }

    public function down(): void
    {
        // Absolute hosts cannot be restored.
    }

    private static function sitePath(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (! str_starts_with($path, '/storage/') && ! str_starts_with($path, '/img/')) {
            return null;
        }

        return $path;
    }
};
