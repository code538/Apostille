<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceDocumentRequirement extends Model
{
    protected $fillable = [
        'service_id',
        'country_id',
        'region_id',
        'document_type',
        'title',
        'description',
        'is_required',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order'  => 'integer',
        'status'      => 'string',
    ];

    /**
     * Service this document requirement belongs to.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class,
            'service_id'
        );
    }

    /**
     * Country this requirement applies to.
     *
     * NULL means it is a global requirement
     * for the service.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(
            Country::class,
            'country_id'
        );
    }

    /**
     * Region this requirement applies to.
     *
     * NULL means it applies to the whole country.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(
            Region::class,
            'region_id'
        );
    }

    /**
     * Check whether the requirement is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check whether this document is mandatory.
     */
    public function isRequired(): bool
    {
        return $this->is_required === true;
    }

    /**
     * Check whether this requirement is global
     * for the service.
     */
    public function isGlobal(): bool
    {
        return is_null($this->country_id);
    }

    /**
     * Check whether this requirement applies
     * to the whole country.
     */
    public function isCountryWide(): bool
    {
        return !is_null($this->country_id)
            && is_null($this->region_id);
    }

    /**
     * Check whether this requirement is
     * specific to a region.
     */
    public function isRegionSpecific(): bool
    {
        return !is_null($this->country_id)
            && !is_null($this->region_id);
    }
}