<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class HeroSlide extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'movie_id',
        'internal_title',
        'desktop_image',
        'mobile_image',
        'display_order',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'display_order' => 'integer',
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
