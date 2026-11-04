<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_METHODS = [
        'esewa' => 'eSewa',
        'khalti' => 'Khalti',
        'card' => 'Credit / Debit Card',
        'counter' => 'Pay at Counter',
    ];

    protected $fillable = [
        'booking_code',
        'user_id',
        'movie_id',
        'cinema_id',
        'hall_id',
        'showtime_id',
        'show_date',
        'start_time',
        'movie_title',
        'cinema_name',
        'hall_name',
        'format',
        'customer_name',
        'customer_email',
        'customer_phone',
        'seat_count',
        'subtotal',
        'discount_amount',
        'total_amount',
        'offer_code',
        'payment_method',
        'status',
        'is_paid',
        'checked_in_at',
    ];

    protected $casts = [
        'show_date' => 'date',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'is_paid' => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
     * @return BelongsTo<Showtime, $this>
     */
    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }

    /**
     * @return HasMany<BookingSeat, $this>
     */
    public function seats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->latest();
    }

    public static function generateCode(): string
    {
        do {
            $code = 'CG-'.strtoupper(substr(bin2hex(random_bytes(6)), 0, 8));
        } while (self::where('booking_code', $code)->exists());

        return $code;
    }

    public function status_label(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Confirmed',
        };
    }

    public function payment_label(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? '—';
    }

    public function seat_labels(): string
    {
        return $this->seats->pluck('seat_label')->implode(', ');
    }

    public function is_upcoming(): bool
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return false;
        }

        return $this->show_date->isFuture()
            || ($this->show_date->isToday() && now()->format('H:i:s') <= $this->start_time);
    }
}
