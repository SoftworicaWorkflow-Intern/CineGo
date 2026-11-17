<?php

namespace App\Models;

use App\Events\SeatAvailabilityChanged;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SeatLock extends Model
{
    public const TTL_MINUTES = 5;

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
        $expiredSeatsByShowtime = DB::transaction(function (): array {
            $expiredLocks = self::query()
                ->where('expires_at', '<=', now())
                ->lockForUpdate()
                ->get(['id', 'showtime_id', 'seat_id']);

            if ($expiredLocks->isEmpty()) {
                return [];
            }

            self::query()->whereKey($expiredLocks->modelKeys())->delete();

            return $expiredLocks
                ->groupBy('showtime_id')
                ->map(fn ($locks): array => $locks->pluck('seat_id')->map(fn ($seatId): int => (int) $seatId)->values()->all())
                ->all();
        });

        foreach ($expiredSeatsByShowtime as $showtimeId => $seatIds) {
            SeatAvailabilityChanged::dispatch(
                (int) $showtimeId,
                $seatIds,
                'available',
            );
        }
    }
}
