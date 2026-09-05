<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMethod;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DeliveryMethodController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get all delivery methods.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $query = DeliveryMethod::query()
                ->withCount('rates');

            /*
             * Search.
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

            /*
             * Filter by type.
             */
            if ($request->filled('type')) {

                $query->where(
                    'type',
                    $request->input('type')
                );
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

            $perPage = min(
                (int) $request->input('per_page', 15),
                100
            );

            $deliveryMethods = $query
                ->latest('id')
                ->paginate($perPage);

            return $this->response->success(
                $deliveryMethods,
                'Delivery methods retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve delivery methods.',
                500
            );
        }
    }

    /**
     * Store delivery method.
     */
    public function store(Request $request): JsonResponse
    { 
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
                'unique:delivery_methods,name',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:delivery_methods,slug',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'type' => [
                'required',
                Rule::in([
                    'digital',
                    'courier',
                    'postal',
                    'pickup',
                ]),
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

            $deliveryMethod = DB::transaction(
                function () use ($validated) {

                    $slug = $validated['slug']
                        ?? Str::slug($validated['name']);

                    /*
                     * Make generated slug unique.
                     */
                    $originalSlug = $slug;
                    $counter = 1;

                    while (
                        DeliveryMethod::where(
                            'slug',
                            $slug
                        )->exists()
                    ) {
                        $slug = $originalSlug
                            . '-' . $counter;

                        $counter++;
                    }

                    return DeliveryMethod::create([
                        'name' => $validated['name'],

                        'slug' => $slug,

                        'description'
                            => $validated['description']
                            ?? null,

                        'type' => $validated['type'],

                        'status'
                            => $validated['status']
                            ?? 'active',
                    ]);
                }
            );

            return $this->response->created(
                $deliveryMethod,
                'Delivery method created successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to create delivery method.',
                500
            );
        }
    }

    /**
     * Get single delivery method.
     */
    public function show(
        DeliveryMethod $deliveryMethod
    ): JsonResponse {
        try {

            $deliveryMethod->load([
                'rates',
            ]);

            return $this->response->success(
                $deliveryMethod,
                'Delivery method retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve delivery method.',
                500
            );
        }
    }

    /**
     * Update delivery method.
     */
    public function update(
        Request $request,
        DeliveryMethod $deliveryMethod
    ): JsonResponse {
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    'delivery_methods',
                    'name'
                )->ignore($deliveryMethod->id),
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(
                    'delivery_methods',
                    'slug'
                )->ignore($deliveryMethod->id),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'type' => [
                'required',
                Rule::in([
                    'digital',
                    'courier',
                    'postal',
                    'pickup',
                ]),
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

            /*
             * Make sure generated slug is unique.
             */
            $originalSlug = $slug;
            $counter = 1;

            while (
                DeliveryMethod::where('slug', $slug)
                    ->where(
                        'id',
                        '!=',
                        $deliveryMethod->id
                    )
                    ->exists()
            ) {
                $slug = $originalSlug
                    . '-' . $counter;

                $counter++;
            }

            $deliveryMethod->update([
                'name' => $validated['name'],

                'slug' => $slug,

                'description'
                    => $validated['description']
                    ?? null,

                'type' => $validated['type'],

                'status'
                    => $validated['status']
                    ?? $deliveryMethod->status,
            ]);

            return $this->response->success(
                $deliveryMethod->fresh(),
                'Delivery method updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update delivery method.',
                500
            );
        }
    }

    /**
     * Delete delivery method.
     */
    public function destroy(
        DeliveryMethod $deliveryMethod
    ): JsonResponse {
        try {

            /*
             * Do not delete a delivery method if
             * rates already exist.
             */
            if ($deliveryMethod->rates()->exists()) {

                return $this->response->error(
                    'This delivery method cannot be deleted because delivery rates already exist.',
                    409
                );
            }

            $deliveryMethod->delete();

            return $this->response->noContent(
                'Delivery method deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to delete delivery method.',
                500
            );
        }
    }

    /**
     * Activate / deactivate delivery method.
     */
    public function statusChange(
        DeliveryMethod $deliveryMethod
    ): JsonResponse {
        try {

            $status = $deliveryMethod->status === 'active'
                ? 'inactive'
                : 'active';

            $deliveryMethod->update([
                'status' => $status,
            ]);

            return $this->response->success(
                $deliveryMethod->fresh(),
                $status === 'active'
                    ? 'Delivery method activated successfully.'
                    : 'Delivery method deactivated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update delivery method status.',
                500
            );
        }
    }
}