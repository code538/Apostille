<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Lawyer service coverage records.
     */
    public function lawyerServiceRegions(): HasMany
    {
        return $this->hasMany(
            LawyerServiceRegion::class
        );
    }

    /**
     * Scope active services.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Which services require which documents, and in which countries/regions.
    public function documentRequirements()
    {
        return $this->hasMany(
            ServiceDocumentRequirement::class
        );
    }
}
