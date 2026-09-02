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
        'region_id',
        'fee',
        'currency',
        'estimated_days',
        'status',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'estimated_days' => 'integer',
    ];

    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryMethod::class
        );
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(
            Country::class
        );
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(
            Region::class
        );
    }
}