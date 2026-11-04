<?php

namespace App\Models;

use Database\Factories\SeatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    /** @use HasFactory<SeatFactory> */
    use HasFactory;

    public const TYPES = ['normal', 'premium', 'recliner'];

    protected $fillable = [
        'hall_id',
        'row_label',
        'seat_number',
        'seat_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Hall, $this>
     */
    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * @return HasMany<BookingSeat, $this>
     */
    public function bookingSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }

    /**
     * @return HasMany<SeatLock, $this>
     */
    public function locks(): HasMany
    {
        return $this->hasMany(SeatLock::class);
    }

    public function label(): string
    {
        return $this->row_label.$this->seat_number;
    }

    public function type_label(): string
    {
        return match ($this->seat_type) {
            'premium' => 'Premium',
            'recliner' => 'Recliner',
            default => 'Normal',
        };
    }
}
