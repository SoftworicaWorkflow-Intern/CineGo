<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class HeroSlide extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'movie_id',
        'desktop_image',
        'mobile_image',
        'movie_title_en',
        'movie_title_np',
        'title_en',
        'title_np',
        'description_en',
        'description_np',
        'label_en',
        'label_np',
        'duration',
        'language',
        'release_date',
        'primary_button_text_en',
        'primary_button_text_np',
        'primary_button_url',
        'secondary_button_text_en',
        'secondary_button_text_np',
        'secondary_button_url',
        'display_order',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'display_order' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * @return BelongsTo<Movie, $this>
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        $now = now();

        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function localized_movie_title(): string
    {
        return App::isLocale('np') && $this->movie_title_np ? $this->movie_title_np : $this->movie_title_en;
    }

    public function localized_title(): string
    {
        return App::isLocale('np') && $this->title_np ? $this->title_np : $this->title_en;
    }

    public function localized_description(): string
    {
        return App::isLocale('np') && $this->description_np ? $this->description_np : $this->description_en;
    }

    public function localized_label(): ?string
    {
        return App::isLocale('np') && $this->label_np ? $this->label_np : $this->label_en;
    }

    public function localized_primary_button_text(): string
    {
        return App::isLocale('np') && $this->primary_button_text_np
            ? $this->primary_button_text_np
            : $this->primary_button_text_en;
    }

    public function localized_secondary_button_text(): ?string
    {
        return App::isLocale('np') && $this->secondary_button_text_np
            ? $this->secondary_button_text_np
            : $this->secondary_button_text_en;
    }

    public function duration_label(): string
    {
        if ($this->duration === null) {
            return '';
        }

        $hours = intdiv($this->duration, 60);
        $minutes = $this->duration % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    public function desktop_image_url(): ?string
    {
        return $this->image_url($this->desktop_image);
    }

    public function mobile_image_url(): ?string
    {
        return $this->image_url($this->mobile_image ?: $this->desktop_image);
    }

    /**
     * @return list<string>
     */
    public function uploaded_image_paths(): array
    {
        return collect([$this->desktop_image, $this->mobile_image])
            ->filter(fn (?string $path): bool => $path !== null && str_starts_with($path, 'hero-slides/'))
            ->unique()
            ->values()
            ->all();
    }

    private function image_url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'img/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}
