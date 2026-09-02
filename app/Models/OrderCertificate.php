<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'government_submission_id',
        'certificate_type',
        'certificate_number',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'status',
        'uploaded_by',
        'received_at',
        'verified_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'received_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class
        );
    }

    public function governmentSubmission(): BelongsTo
    {
        return $this->belongsTo(
            GovernmentSubmission::class
        );
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}