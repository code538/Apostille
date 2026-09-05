<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryMethodRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_method_id',
        'country_id',
        'price',
        'currency',
        'estimated_days',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'estimated_days' => 'integer',
        'status' => 'string',
    ];

    /**
     * Delivery method.
     */
    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryMethod::class,
            'delivery_method_id'
        );
    }

    /**
     * Country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(
            Country::class,
            'country_id'
        );
    }

    /**
     * Check whether rate is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}