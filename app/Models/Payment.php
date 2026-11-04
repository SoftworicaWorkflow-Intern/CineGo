<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const PROVIDERS = [
        'esewa' => 'eSewa',
        'khalti' => 'Khalti',
        'card' => 'Card Gateway',
        'counter' => 'Pay at Counter',
    ];

    protected $fillable = [
        'booking_id',
        'user_id',
        'provider',
        'reference',
        'amount',
        'currency',
        'status',
        'provider_payload',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'provider_payload' => 'array',
        'verified_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider_label(): string
    {
        return self::PROVIDERS[$this->provider] ?? $this->provider;
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'PAY-'.strtoupper(substr(bin2hex(random_bytes(8)), 0, 12));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }
}
