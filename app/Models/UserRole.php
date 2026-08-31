<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Pivot
{
    protected $table = 'user_roles';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'role_id',
        'status',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    /**
     * User assigned to this role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Role assigned to the user.
     */
   public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->using(UserRole::class)
            ->withPivot([
                'status',
                'joined_at',
            ])
            ->withTimestamps();
    }
}

