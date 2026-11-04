<?php

namespace App\Models;

use Database\Factories\ShowtimeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Showtime extends Model
{
    /** @use HasFactory<ShowtimeFactory> */
    use HasFactory;

    public const SEAT_TYPE_PRICES = [
        'normal' => 'price_normal',
        'premium' => 'price_premium',
        'recliner' => 'price_recliner',
    ];

    protected $fillable = [
        'movie_id',
        'cinema_id',
        'hall_id',
        'show_date',
        'start_time',
        'format',
        'language',
        'price_normal',
        'price_premium',
        'price_recliner',
        'is_active',
    ];

    protected $casts = [
        'show_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Movie, $this>
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * @return BelongsTo<Cinema, $this>
     */
    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    /**
     * @return BelongsTo<Hall, $this>
     */
    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
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
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->active()
            ->where('show_date', '>=', now()->toDateString())
            ->orderBy('show_date')
            ->orderBy('start_time');
    }

    public function price_for(string $seatType): float
    {
        $column = self::SEAT_TYPE_PRICES[$seatType] ?? 'price_normal';

        return (float) $this->{$column};
    }

    public function start_time_label(): string
    {
        $hour = (int) substr($this->start_time, 0, 2);
        $minute = substr($this->start_time, 3, 2);
        $period = $hour >= 12 ? 'PM' : 'AM';
        $hour12 = $hour % 12 === 0 ? 12 : $hour % 12;

        return sprintf('%d:%s %s', $hour12, $minute, $period);
    }

    public function is_past(): bool
    {
        if ($this->show_date->isFuture()) {
            return false;
        }

        return $this->show_date->isPast()
            || now()->format('H:i:s') > $this->start_time;
    }
}
