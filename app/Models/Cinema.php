<?php

namespace App\Models;

use Database\Factories\CinemaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class Cinema extends Model
{
    /** @use HasFactory<CinemaFactory> */
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'name_np',
        'slug',
        'address',
        'address_np',
        'phone',
        'email',
        'description',
        'description_np',
        'facilities',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'facilities' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @return HasMany<Hall, $this>
     */
    public function halls(): HasMany
    {
        return $this->hasMany(Hall::class);
    }

    /**
     * @return HasMany<Showtime, $this>
     */
    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function localised_name(): string
    {
        return App::isLocale('np') && $this->name_np ? $this->name_np : $this->name;
    }

    public function localised_address(): string
    {
        return App::isLocale('np') && $this->address_np ? $this->address_np : $this->address;
    }
}
