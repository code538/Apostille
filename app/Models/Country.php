<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'iso2',
        'iso3',
        'phone_code',
        'currency_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Regions belonging to country.
     */
    public function regions(): HasMany
    {
        return $this->hasMany(
            Region::class
        );
    }
}