<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawyerServiceRegion extends Model
{
    protected $fillable = [
        'lawyer_profile_id',
        'service_id',
        'country_id',
        'region_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Lawyer profile.
     */
    public function lawyerProfile(): BelongsTo
    {
        return $this->belongsTo(
            LawyerProfile::class
        );
    }

    /**
     * Service.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class
        );
    }

    /**
     * Country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(
            Country::class
        );
    }

    /**
     * Region.
     *
     * Nullable because the lawyer can provide
     * the service country-wide.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(
            Region::class
        );
    }

    /**
     * Check whether this coverage is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check whether this is a country-wide service.
     */
    public function isCountryWide(): bool
    {
        return is_null($this->region_id);
    }

    public function pricings()
    {
        return $this->hasMany(
            LawyerServicePricing::class
        );
    }
}
