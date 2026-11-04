<?php

namespace App\Models;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'name_np',
        'slug',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<Cinema, $this>
     */
    public function cinemas(): HasMany
    {
        return $this->hasMany(Cinema::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function display_name(): string
    {
        return App::isLocale('np') ? $this->name_np : $this->name;
    }
}
