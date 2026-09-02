<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceDocumentRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'document_type',
        'name',
        'description',
        'is_required',
        'allow_multiple',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'allow_multiple' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Service to which this requirement belongs.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}