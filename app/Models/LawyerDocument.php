<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawyerDocument extends Model
{
    protected $fillable = [
        'lawyer_profile_id',
        'document_type',
        'document_number',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'expires_at',
        'reviewer_notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at' => 'date',
        'file_size' => 'integer',
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
     * User who verified the document.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    /**
     * Check whether document is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check whether document is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }
}
