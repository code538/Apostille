<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get all services.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Service::query();

            /*
             * Search by service name.
             */
            if ($request->filled('search')) {
                $search = $request->input('search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            /*
             * Filter by status.
             */
            if ($request->filled('status')) {
                $query->where(
                    'status',
                    $request->input('status')
                );
            }

            /*
             * Pagination.
             */
            $perPage = min(
                (int) $request->input('per_page', 15),
                100
            );

            $services = $query
                ->latest('id')
                ->paginate($perPage);

            return $this->response->success(
                $services,
                'Services retrieved successfully.'
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
     * Store a new service.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:services,name',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:services,slug',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ]);

        try {

            $service = DB::transaction(function () use ($validated) {

                $slug = $validated['slug']
                    ?? Str::slug($validated['name']);

                /*
                 * Make sure automatically generated slug
                 * is also unique.
                 */
                $originalSlug = $slug;
                $counter = 1;

                while (
                    Service::where('slug', $slug)->exists()
                ) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                return Service::create([
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'description' => $validated['description'] ?? null,
                    'status' => $validated['status'] ?? 'active',
                ]);
            });

            return $this->response->created(
                $service,
                'Service created successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to create service.',
                500
            );
        }
    }

    /**
     * Get a single service.
     */
    public function show(Service $service): JsonResponse
    {
        try {

            return $this->response->success(
                $service,
                'Service retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve service.',
                500
            );
        }
    }

    /**
     * Update a service.
     */
    public function update(
        Request $request,
        Service $service
    ): JsonResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')
                    ->ignore($service->id),
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('services', 'slug')
                    ->ignore($service->id),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ]);

        try {

            $slug = $validated['slug']
                    ?? Str::slug($validated['name']);
            $service->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description']
                    ?? null,
                'status' => $validated['status']
                    ?? $service->status,
            ]);

            return $this->response->success(
                $service->fresh(),
                'Service updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update service.',
                500
            );
        }
    }

    /**
     * Delete a service.
     */
    public function destroy(Service $service): JsonResponse
    {
        try {

            /*
             * Do not delete a service if lawyers
             * are already using it.
             */
            if ($service->lawyerServiceRegions()->exists()) {
                return $this->response->error(
                    'This service cannot be deleted because it is already assigned to one or more lawyers.',
                    409
                );
            }

            $service->delete();

            return $this->response->noContent(
                'Service deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to delete service.',
                500
            );
        }
    }

    /**
     * Activate a service.
     */
    public function statusChange(Service $service): JsonResponse
    {
        try {

            $serviceStatus = $service->status === 'active'
                ? 'inactive'
                : 'active';

            $service->update([
                'status' => $serviceStatus,
            ]);

            return $this->response->success(
                $service->fresh(),
                $serviceStatus === 'active'
                    ? 'Service activated successfully.'
                    : 'Service deactivated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update service status.',
                500
            );
        }
    }

}