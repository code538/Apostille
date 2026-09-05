<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'order_id',
    //     'invoice_number',
    //     'status',
    //     'subtotal',
    //     'tax',
    //     'discount',
    //     'total_amount',
    //     'paid_amount',
    //     'due_amount',
    //     'currency',
    //     'issued_at',
    //     'due_at',
    //     'paid_at',
    // ];

    // protected $casts = [
    //     'subtotal' => 'decimal:2',
    //     'tax' => 'decimal:2',
    //     'discount' => 'decimal:2',
    //     'total_amount' => 'decimal:2',
    //     'paid_amount' => 'decimal:2',
    //     'due_amount' => 'decimal:2',

    //     'issued_at' => 'datetime',
    //     'due_at' => 'datetime',
    //     'paid_at' => 'datetime',
    // ];

    // public function order(): BelongsTo
    // {
    //     return $this->belongsTo(
    //         Order::class
    //     );
    // }

    // public function items(): HasMany
    // {
    //     return $this->hasMany(
    //         InvoiceItem::class
    //     );
    // }

    // public function payments(): HasMany
    // {
    //     return $this->hasMany(
    //         Payment::class
    //     );
    // }

      protected $fillable = [
        'order_id',
        'customer_id',
        'invoice_number',
        'status',
        'currency',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'issued_at',
        'due_at',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',

        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class,
            'order_id'
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'customer_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            InvoiceItem::class,
            'invoice_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class,
            'invoice_id'
        );
    }
}