<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'two_factor_enabled',
        'email_verified_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * User's roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot([
                'status',
                'joined_at',
            ])
            ->withTimestamps();
    }

    /**
     * Get the user's active role.
     *
     * Since your current user_roles table has a unique
     * constraint on user_id, a user can have one role.
     */
    public function role(): ?Role
    {
        return $this->roles()
            ->wherePivot('status', 'active')
            ->first();
    }

    /**
     * Check whether the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()
            ->where('slug', $role)
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Check whether the user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()
            ->whereIn('slug', $roles)
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Check whether the user is a Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check whether the user is an Administrator.
     */
    public function isAdministrator(): bool
    {
        return $this->hasRole('administrator');
    }

    /**
     * Check whether the user is a Lawyer.
     */
     public function isApostilleOfficer(): bool
    {
        return $this->hasRole('apostille-officer');
    }

    /**
     * Check whether the user is a Customer.
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Check whether the user is a Business Client.
     */
    public function isBusinessClient(): bool
    {
        return $this->hasRole('business-client');
    }

    /**
     * Check whether the user is Customer Support.
     */
    public function isCustomerSupport(): bool
    {
        return $this->hasRole('customer-support');
    }

    /**
     * Check whether the user is Finance.
     */
    public function isFinance(): bool
    {
        return $this->hasRole('finance');
    }

    /**
     * Check whether the user is Courier.
     */
    public function isCourier(): bool
    {
        return $this->hasRole('courier');
    }

    public function lawyerProfile(): HasOne
    {
        return $this->hasOne(
            LawyerProfile::class
        );
    }


    public function orders()
    {
        return $this->hasMany(
            Order::class
        );
    }

    public function assignedOrders()
    {
        return $this->hasMany(
            Order::class,
            'assigned_officer_id'
        );
    }

    public function orderAssignments()
    {
        return $this->hasMany(
            OrderAssignment::class,
            'assigned_to'
        );
    }

    public function createdCharges()
    {
        return $this->hasMany(
            OrderCharge::class,
            'created_by'
        );
    }

    public function payments()
    {
        return $this->hasMany(
            Payment::class
        );
    }

    public function businessProfile()
    {
        return $this->hasOne(
            BusinessProfile::class
        );
    }
  
}

