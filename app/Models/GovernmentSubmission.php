<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GovernmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'submitted_by',
        'government_department',
        'portal_name',
        'application_reference',
        'status',
        'submitted_at',
        'completed_at',
        'last_checked_at',
        'submission_notes',
        'response_notes',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class
        );
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by'
        );
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(
            OrderCertificate::class
        );
    }
}