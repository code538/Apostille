<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_method_id',
        'fee',
        'currency',
        'recipient_name',
        'phone',
        'email',
        'address_line_1',
        'address_line_2',
        'city',
        'postal_code',
        'country_id',
        'region_id',
        'tracking_number',
        'courier_name',
        'status',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class
        );
    }

    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryMethod::class
        );
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(
            Country::class
        );
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(
            Region::class
        );
    }
}