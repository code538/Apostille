<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LawyerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'professional_name',
        'bar_registration_number',
        'bar_council_name',
        'law_firm_name',
        'professional_bio',
        'country_id',
        'region_id',
        'address_line_1',
        'address_line_2',
        'city',
        'postal_code',
        'years_of_experience',
        'website',
        'profile_photo',
        'approval_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'approved_at',
        'is_available',
        'is_active',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'years_of_experience' => 'integer',
    ];

    /**
     * User account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Primary country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Primary region.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function serviceRegions(): HasMany {
         return $this->hasMany( LawyerServiceRegion::class ); 
    }

    /**
     * Admin who reviewed the profile.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    /**
     * Lawyer documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(
            LawyerDocument::class
        );
    }

    /**
     * Check whether lawyer is approved.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check whether lawyer can receive orders.
     */
    public function canReceiveOrders(): bool
    {
        return $this->isApproved()
            && $this->is_active
            && $this->is_available;
    }
}
