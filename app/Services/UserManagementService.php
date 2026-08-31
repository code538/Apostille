<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    /**
     * Get users with filters.
     */
    public function getUsers(
        array $filters = []
    ): LengthAwarePaginator {

        $query = User::query()
            ->with([
                'roles',
            ]);

        /*
         * Search.
         */
        if (! empty($filters['search'])) {

            $search = $filters['search'];

            $query->where(function ($query) use ($search) {

                $query->where(
                    'name',
                    'like',
                    "%{$search}%"
                );

                $query->orWhere(
                    'email',
                    'like',
                    "%{$search}%"
                );

                $query->orWhere(
                    'phone',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
         * Filter by user type.
         */
        if (! empty($filters['user_type'])) {

            $query->where(
                'user_type',
                $filters['user_type']
            );
        }

        /*
         * Filter by status.
         */
        if (! empty($filters['status'])) {

            $query->where(
                'status',
                $filters['status']
            );
        }

        /*
         * Filter by role.
         */
        if (! empty($filters['role'])) {

            $query->whereHas(
                'roles',
                function ($query) use ($filters) {

                    $query->where(
                        'roles.slug',
                        $filters['role']
                    );
                }
            );
        }

        return $query
            ->latest()
            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Get complete user details.
     */
    public function getUserDetails(User $user): User
    {
        return $user->load([
            'roles',
        ]);
    }

    /**
     * Create staff member.
     */
    public function createStaff(
        array $data,
        User $creator
    ): User {

        /*
         * Only Super Admin and Administrator
         * can create staff.
         */
        if (! $creator->hasAnyRole([
            'super-admin',
            'administrator',
        ])) {
            throw ValidationException::withMessages([
                'role' => [
                    'You are not authorized to create staff.'
                ],
            ]);
        }

        /*
         * Staff roles allowed from this endpoint.
         */
        $allowedRoles = [
            'customer-support',
            'finance',
            'courier',
        ];

        if (! in_array(
            $data['role'],
            $allowedRoles,
            true
        )) {
            throw ValidationException::withMessages([
                'role' => [
                    'Invalid staff role.'
                ],
            ]);
        }

        /*
         * Create user and role together.
         */
        return DB::transaction(function () use (
            $data
        ) {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'user_type' => 'staff',
                'status' => 'active',
            ]);

            $role = Role::where(
                'slug',
                $data['role']
            )->firstOrFail();

            $user->roles()->attach(
                $role->id,
                [
                    'status' => 'active',
                    'joined_at' => now(),
                ]
            );

            return $user->load('roles');
        });
    }

    /**
     * Update user.
     */
    public function updateUser(
        User $user,
        array $data,
        User $updater
    ): User {

        $this->ensureCanManageUser(
            $updater,
            $user
        );

        $user->update($data);

        return $user->fresh('roles');
    }

    /**
     * Update user status.
     */
    public function updateStatus(
        User $user,
        string $status,
        User $updater
    ): User {

        $this->ensureCanManageUser(
            $updater,
            $user
        );

        $user->update([
            'status' => $status,
        ]);

        /*
         * If blocked/inactive, invalidate
         * all API tokens.
         */
        if ($status !== 'active') {
            $user->tokens()->delete();
        }

        return $user->fresh('roles');
    }

    /**
     * Delete user.
     */
    public function deleteUser(
        User $user,
        User $deleter
    ): void {

        $this->ensureCanManageUser(
            $deleter,
            $user
        );

        /*
         * Do not physically delete users.
         * For this type of system, deactivation
         * is safer.
         */
        $user->update([
            'status' => 'inactive',
        ]);

        $user->tokens()->delete();
    }

    /**
     * Ensure creator can manage target user.
     */
    protected function ensureCanManageUser(
        User $updater,
        User $target
    ): void {

        /*
         * Cannot manage yourself through this
         * administrative endpoint.
         */
        if ($updater->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => [
                    'You cannot perform this action on yourself.'
                ],
            ]);
        }

        /*
         * Super Admin can manage everyone.
         */
        if ($updater->isSuperAdmin()) {
            return;
        }

        /*
         * Administrator cannot manage
         * Super Admin or another Administrator.
         */
        if ($updater->isAdministrator()) {

            if ($target->hasAnyRole([
                'super-admin',
                'administrator',
            ])) {
                throw ValidationException::withMessages([
                    'user' => [
                        'You are not authorized to manage this user.'
                    ],
                ]);
            }

            return;
        }

        throw ValidationException::withMessages([
            'user' => [
                'You are not authorized to manage users.'
            ],
        ]);
    }
}