<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawyerServicePricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'lawyer_service_region_id',
        'service_level',
        'fee',
        'currency',
        'estimated_days',
        'status',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'estimated_days' => 'integer',
        'status' => 'string',
    ];

    /**
     * Lawyer service region.
     */
    public function lawyerServiceRegion(): BelongsTo
    {
        return $this->belongsTo(
            LawyerServiceRegion::class
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}