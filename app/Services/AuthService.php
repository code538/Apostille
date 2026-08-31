<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Register a new customer.
     */
    public function register(array $data)
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'status' => 'active',
            ]);
            
            $customerRole = Role::where('slug', $data['user_type'])->firstOrFail();
            //$customerRole = Role::where('slug', 'customer')->first();

            if ($customerRole) {
                $user->roles()->attach($customerRole->id, [
                    'status' => 'active',
                    'joined_at' => now(),
                ]);
            }

            /*
             * Create API token.
             */
            $token = $user->createToken(
                'api-token'
            )->plainTextToken;

            return [
                'user' => $user->load('roles'),
                'token' => $token,
                'token_type' => 'Bearer',
            ];
        });
    }

    /**
     * Login user.
     */
    public function login(
        string $email,
        string $password
    ) {
        $user = User::where('email', $email)->first();

        if (
            ! $user ||
            ! Hash::check($password, $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

        /*
         * Update last login.
         */
        $user->update([
            'last_login_at' => now(),
        ]);

        /*
         * Create new API token.
         */
        $token = $user->createToken(
            'api-token'
        )->plainTextToken;

        return [
            'user' => $user->load('roles'),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Logout current device/token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Logout from all devices.
     */
    public function logoutFromAllDevices(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Send forgot password link.
     */
    public function forgotPassword(string $email): string
    {
        $status = Password::sendResetLink([
            'email' => $email,
        ]);

        return $status;
    }

    /**
     * Reset password.
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password) {

                $user->forceFill([
                    'password' => $password,
                ])->save();

                /*
                 * Invalidate all existing API tokens
                 * after password reset.
                 */
                $user->tokens()->delete();
            }
        );

        return $status;
    }
}
