<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatLock extends Model
{
    public const TTL_MINUTES = 10;

    protected $fillable = [
        'showtime_id',
        'seat_id',
        'holder',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Showtime, $this>
     */
    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }

    /**
     * @return BelongsTo<Seat, $this>
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Locks that are still holding their seats.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Remove expired locks so their seats become bookable again.
     */
    public static function purgeExpired(): void
    {
        self::where('expires_at', '<=', now())->delete();
    }
}
