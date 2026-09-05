<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'lawyer_profile_id',
        'service_id',
        'country_id',
        'region_id',
        'lawyer_service_region_id',
        'service_level',
        'delivery_method_id',
        'status',
        'payment_status',
        'subtotal',
        'delivery_fee',
        'additional_fee',
        'discount',
        'tax',
        'total_amount',
        'currency',
        'customer_notes',
        'internal_notes',
        'assigned_officer_id',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'additional_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',

        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Customer
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lawyer
    |--------------------------------------------------------------------------
    */

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(
            LawyerProfile::class,
            'lawyer_profile_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Service
    |--------------------------------------------------------------------------
    */

    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Country
    |--------------------------------------------------------------------------
    */

    public function country(): BelongsTo
    {
        return $this->belongsTo(
            Country::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Region
    |--------------------------------------------------------------------------
    */

    public function region(): BelongsTo
    {
        return $this->belongsTo(
            Region::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lawyer Service Region
    |--------------------------------------------------------------------------
    */

    public function lawyerServiceRegion(): BelongsTo
    {
        return $this->belongsTo(
            LawyerServiceRegion::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delivery Method
    |--------------------------------------------------------------------------
    */

    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryMethod::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Assigned Apostille Officer
    |--------------------------------------------------------------------------
    */

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_officer_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Order Assignments
    |--------------------------------------------------------------------------
    */

    public function assignments(): HasMany
    {
        return $this->hasMany(
            OrderAssignment::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Order Charges
    |--------------------------------------------------------------------------
    */

    public function charges(): HasMany
    {
        return $this->hasMany(
            OrderCharge::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Order Documents
    |--------------------------------------------------------------------------
    */

    public function documents(): HasMany
    {
        return $this->hasMany(
            OrderDocument::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Document Requests
    |--------------------------------------------------------------------------
    */

    public function documentRequests(): HasMany
    {
        return $this->hasMany(
            DocumentRequest::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delivery
    |--------------------------------------------------------------------------
    */

    public function delivery(): HasOne
    {
        return $this->hasOne(
            OrderDelivery::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status History
    |--------------------------------------------------------------------------
    */

    public function statusHistories(): HasMany
    {
        return $this->hasMany(
            OrderStatusHistory::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Invoices
    |--------------------------------------------------------------------------
    */

    public function invoices(): HasMany
    {
        return $this->hasMany(
            Invoice::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Government Submissions
    |--------------------------------------------------------------------------
    */

    public function governmentSubmissions(): HasMany
    {
        return $this->hasMany(
            GovernmentSubmission::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Certificates
    |--------------------------------------------------------------------------
    */

    public function certificates(): HasMany
    {
        return $this->hasMany(
            OrderCertificate::class
        );
    }

    public function lawyerServicePricing(): BelongsTo
    {
        return $this->belongsTo(
            LawyerServicePricing::class,
            'lawyer_service_pricing_id'
        );
    }

  
}