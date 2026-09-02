<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get active services available to customers.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $query = Service::query()
                ->where('status', 'active');

            /*
             * Search by service name.
             */
            if ($request->filled('search')) {

                $search = $request->input('search');

                $query->where(function ($query) use ($search) {

                    $query->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'slug',
                        'like',
                        "%{$search}%"
                    );
                });
            }

            $perPage = min(
                (int) $request->input('per_page', 15),
                100
            );

            $services = $query
                ->select([
                    'id',
                    'name',
                    'slug',
                    'description',
                ])
                ->orderBy('name')
                ->paginate($perPage);

            return $this->response->success(
                $services,
                'Available services retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve services.',
                500
            );
        }
    }

    /**
     * Get service details and required documents.
     */
    public function show(Service $service): JsonResponse
    {
        try {

            if ($service->status !== 'active') {

                return $this->response->error(
                    'Service is not available.',
                    404
                );
            }

            $service->load([
                'documentRequirements' => function ($query) {

                    $query
                        ->where('status', 'active')
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ]);

            return $this->response->success(
                $service,
                'Service details retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve service details.',
                500
            );
        }
    }
}