<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Users assigned to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->using(UserRole::class)
            ->withPivot([
                'status',
                'joined_at',
            ])
            ->withTimestamps();
    }

    /**
     * Scope active roles.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('users', function ($query) {
            $query->where('user_roles.status', 'active');
        });
    }
}
