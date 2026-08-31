<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserManagementService $userService,
        protected ApiResponseService $response
    ) {
    }

    /**
     * Display users.
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getUsers(
            $request->all()
        );

        return $this->response->success(
            $users,
            'Users retrieved successfully.'
        );
    }

    /**
     * Display a specific user.
     */
    public function show(User $user): JsonResponse
    {
        $user = $this->userService->getUserDetails(
            $user
        );

        return $this->response->success(
            $user,
            'User details retrieved successfully.'
        );
    }

    /**
     * Create staff member.
     */
    public function storeStaff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255',],
            'email' => ['required','email','max:255','unique:users,email',],
            'phone' => ['nullable','string','max:30',],
            'password' => ['required','string','min:8',],
            'role' => ['required','string','in:customer-support,finance,courier',],
        ]);
        //dd('okk');
        try {

            $user = $this->userService->createStaff(
                $validated,
                $request->user()
            );

            return $this->response->created(
                $user,
                'Staff member created successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                $e->getMessage(),
                422
            );
        }
    }

    /**
     * Update user.
     */
    public function update(
        Request $request,
        User $user
    ): JsonResponse {

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
        ]);

        try {

            $user = $this->userService->updateUser(
                $user,
                $validated,
                $request->user()
            );

            return $this->response->success(
                $user,
                'User updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Unable to update user.',
                422
            );
        }
    }

    /**
     * Update user status.
     */
    public function updateStatus(
        Request $request,
        User $user
    ): JsonResponse {

        $validated = $request->validate([
            'status' => [
                'required',
                'in:active,inactive,blocked',
            ],
        ]);

        try {

            $user = $this->userService->updateStatus(
                $user,
                $validated['status'],
                $request->user()
            );

            return $this->response->success(
                $user,
                'User status updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                $e->getMessage(),
                422
            );
        }
    }

    /**
     * Delete/deactivate user.
     */
    public function destroy(
        Request $request,
        User $user
    ): JsonResponse {

        try {

            $this->userService->deleteUser(
                $user,
                $request->user()
            );

            return $this->response->success(
                null,
                'User deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                $e->getMessage(),
                422
            );
        }
    }
}