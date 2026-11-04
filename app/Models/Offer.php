<?php

namespace App\Models;

use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'title_np',
        'description',
        'description_np',
        'code',
        'badge',
        'discount_type',
        'discount_value',
        'min_amount',
        'valid_from',
        'valid_to',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'min_amount' => 'float',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCurrentlyValid(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $builder) {
                $builder->whereNull('valid_from')->orWhere('valid_from', '<=', now()->toDateString());
            })
            ->where(function (Builder $builder) {
                $builder->whereNull('valid_to')->orWhere('valid_to', '>=', now()->toDateString());
            });
    }

    public function localised_title(): string
    {
        return App::isLocale('np') && $this->title_np ? $this->title_np : $this->title;
    }

    public function localised_description(): string
    {
        return App::isLocale('np') && $this->description_np ? $this->description_np : $this->description;
    }

    public function badge_label(): string
    {
        if ($this->badge) {
            return $this->badge;
        }

        if ($this->discount_type === 'percentage') {
            return (int) $this->discount_value.'% OFF';
        }

        return 'Rs. '.(int) $this->discount_value.' OFF';
    }

    /**
     * Calculate the discounted amount for the given subtotal.
     */
    public function discount_for(float $subtotal): float
    {
        if ($this->min_amount !== null && $subtotal < $this->min_amount) {
            return 0.0;
        }

        $discount = $this->discount_type === 'percentage'
            ? $subtotal * ($this->discount_value / 100)
            : min($this->discount_value, $subtotal);

        return round($discount, 2);
    }

    public function valid_label(): string
    {
        if ($this->valid_from && $this->valid_to) {
            return 'Valid '.$this->valid_from->format('M j').' – '.$this->valid_to->format('M j, Y');
        }

        if ($this->valid_to) {
            return 'Valid until '.$this->valid_to->format('M j, Y');
        }

        return 'Always available';
    }
}
