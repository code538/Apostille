<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ApiResponseService
{
    public function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function error(
        string $message = 'Something went wrong',
        int $status = 500,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    public function created(
        mixed $data = null,
        string $message = 'Created successfully'
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    public function noContent(
        string $message = 'Deleted successfully'
    ): JsonResponse {
        return $this->success(null, $message, 200);
    }
}