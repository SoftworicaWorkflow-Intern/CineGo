<?php

namespace App\Models;

use Database\Factories\MovieFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class Movie extends Model
{
    /** @use HasFactory<MovieFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'title_np',
        'slug',
        'synopsis',
        'synopsis_np',
        'genres',
        'duration_minutes',
        'language',
        'release_date',
        'certificate',
        'rating',
        'poster_url',
        'banner_url',
        'trailer_url',
        'director',
        'cast',
        'is_now_showing',
        'is_coming_soon',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'genres' => 'array',
        'release_date' => 'date',
        'rating' => 'float',
        'is_now_showing' => 'boolean',
        'is_coming_soon' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<Showtime, $this>
     */
    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    /**
     * @return HasMany<HeroSlide, $this>
     */
    public function heroSlides(): HasMany
    {
        return $this->hasMany(HeroSlide::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function fans(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeNowShowing(Builder $query): Builder
    {
        return $query->active()->where('is_now_showing', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeComingSoon(Builder $query): Builder
    {
        return $query->active()->where('is_coming_soon', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->active()->where('is_featured', true);
    }

    public function localised_title(): string
    {
        return App::isLocale('np') && $this->title_np ? $this->title_np : $this->title;
    }

    public function localised_synopsis(): string
    {
        return App::isLocale('np') && $this->synopsis_np ? $this->synopsis_np : $this->synopsis;
    }

    public function duration_label(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    public function genre_label(): string
    {
        return implode(', ', $this->genres ?? []);
    }

    public function valid_trailer_url(): ?string
    {
        if (! is_string($this->trailer_url) || filter_var($this->trailer_url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $trailer = parse_url($this->trailer_url);
        $scheme = strtolower($trailer['scheme'] ?? '');
        $host = strtolower($trailer['host'] ?? '');
        $host = str_starts_with($host, 'www.') ? substr($host, 4) : $host;
        $isSupportedHost = $host === 'youtu.be'
            || $host === 'youtube.com'
            || str_ends_with($host, '.youtube.com')
            || $host === 'youtube-nocookie.com'
            || str_ends_with($host, '.youtube-nocookie.com')
            || $host === 'vimeo.com'
            || $host === 'player.vimeo.com';

        return in_array($scheme, ['http', 'https'], true) && $isSupportedHost ? $this->trailer_url : null;
    }

    public function is_released(): bool
    {
        return $this->release_date !== null && $this->release_date->isPast();
    }
}
