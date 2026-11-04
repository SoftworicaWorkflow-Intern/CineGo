<?php

namespace App\Models;

use Database\Factories\HallFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    /** @use HasFactory<HallFactory> */
    use HasFactory;

    protected $fillable = [
        'cinema_id',
        'name',
        'total_rows',
        'seats_per_row',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Cinema, $this>
     */
    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    /**
     * @return HasMany<Seat, $this>
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
