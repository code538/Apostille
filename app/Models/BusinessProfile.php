<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'registration_number',
        'tax_number',
        'contact_person',
        'company_email',
        'company_phone',
        'country_id',
        'region_id',
        'address_line_1',
        'address_line_2',
        'city',
        'postal_code',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
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