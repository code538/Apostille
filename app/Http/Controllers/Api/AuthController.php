<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected ApiResponseService $response
    ) {
    }

    /**
     * Register.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required','string', 'max:255',],
            'email' => ['required','email','max:255','unique:users,email',],
            'phone' => ['nullable','string','max:30',],
            'password' => ['required','string','min:8','confirmed',],
            'user_type' => [ 'required', 'string',],
        ]);
        //dd($validated);
        try {

            $data = $this->authService->register(
                $validated
            );

            return $this->response->created(
                $data,
                'Registration successful.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Registration failed.',
                500
            );
        }
    }

    /**
     * Login.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        try {

            $data = $this->authService->login(
                $validated['email'],
                $validated['password']
            );

            return $this->response->success(
                $data,
                'Login successful.'
            );

        } catch (ValidationException $e) {

            return $this->response->error(
                'The provided credentials are incorrect.',
                422,
                $e->errors()
            );
        }
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return $this->response->success(
            $user,
            'User retrieved successfully.'
        );
    }

    /**
     * Logout current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout(
            $request->user()
        );

        return $this->response->success(
            null,
            'Logout successful.'
        );
    }

    /**
     * Logout from all devices.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutFromAllDevices(
            $request->user()
        );

        return $this->response->success(
            null,
            'Logged out from all devices.'
        );
    }

    /**
     * Forgot password.
     */
    public function forgotPassword(
        Request $request
    ): JsonResponse {

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
            ],
        ]);

        $status = $this->authService->forgotPassword(
            $validated['email']
        );

        /*
         * Do not reveal whether an email exists.
         */
        if (
            $status !== Password::RESET_LINK_SENT
        ) {
            return $this->response->error(
                'Unable to process password reset request.',
                422
            );
        }

        return $this->response->success(
            null,
            'If the email exists, a password reset link has been sent.'
        );
    }

    /**
     * Reset password.
     */
    public function resetPassword(
        Request $request
    ): JsonResponse {

        $validated = $request->validate([
            'token' => [
                'required',
                'string',
            ],

            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $status = $this->authService->resetPassword(
            $validated
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->response->error(
                __($status),
                422
            );
        }

        return $this->response->success(
            null,
            'Password reset successfully.'
        );
    }
}
