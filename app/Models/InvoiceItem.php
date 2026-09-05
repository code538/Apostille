<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'invoice_id',
    //     'item_type',
    //     'description',
    //     'quantity',
    //     'unit_price',
    //     'amount',
    // ];

    // protected $casts = [
    //     'quantity' => 'integer',
    //     'unit_price' => 'decimal:2',
    //     'amount' => 'decimal:2',
    // ];

    // public function invoice(): BelongsTo
    // {
    //     return $this->belongsTo(
    //         Invoice::class
    //     );
    // }

     protected $fillable = [
        'invoice_id',
        'service_id',
        'delivery_method_id',
        'order_charge_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'tax_amount',
        'discount_amount',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(
            Invoice::class,
            'invoice_id'
        );
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class,
            'service_id'
        );
    }

    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryMethod::class,
            'delivery_method_id'
        );
    }

    public function orderCharge(): BelongsTo
    {
        return $this->belongsTo(
            OrderCharge::class,
            'order_charge_id'
        );
    }
}