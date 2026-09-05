<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\DeliveryMethod;
use App\Models\DeliveryMethodRate;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryMethodRateController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get delivery method rates.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $query = DeliveryMethodRate::query()
                ->with([
                    'deliveryMethod:id,name,slug,type,status',
                    'country:id,name,iso2',
                ]);

            /*
             * Filter by delivery method.
             */
            if ($request->filled('delivery_method_id')) {

                $query->where(
                    'delivery_method_id',
                    $request->integer(
                        'delivery_method_id'
                    )
                );
            }

            /*
             * Filter by country.
             */
            if ($request->filled('country_id')) {

                $query->where(
                    'country_id',
                    $request->integer('country_id')
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

            $rates = $query
                ->latest('id')
                ->paginate($perPage);

            return $this->response->success(
                $rates,
                'Delivery method rates retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve delivery method rates.',
                500
            );
        }
    }

    /**
     * Create delivery method rate.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'delivery_method_id' => [
                'required',
                'integer',
                'exists:delivery_methods,id',
            ],

            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'max:10',
            ],

            'estimated_days' => [
                'nullable',
                'integer',
                'min:1',
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

            /*
             * Make sure delivery method exists.
             */
            $deliveryMethod = DeliveryMethod::find(
                $validated['delivery_method_id']
            );

            if (! $deliveryMethod) {

                return $this->response->error(
                    'Delivery method not found.',
                    404
                );
            }

            /*
             * Prevent duplicate country rate.
             *
             * One delivery method can have only
             * one rate per country.
             */
            $exists = DeliveryMethodRate::query()
                ->where(
                    'delivery_method_id',
                    $validated['delivery_method_id']
                )
                ->where(
                    'country_id',
                    $validated['country_id']
                )
                ->exists();

            if ($exists) {

                return $this->response->error(
                    'A delivery rate for this country already exists for this delivery method.',
                    409
                );
            }

            $rate = DeliveryMethodRate::create([
                'delivery_method_id'
                    => $validated['delivery_method_id'],

                'country_id'
                    => $validated['country_id'],

                'price'
                    => $validated['price'],

                'currency'
                    => strtoupper($validated['currency']),

                'estimated_days'
                    => $validated['estimated_days']
                    ?? null,

                'status'
                    => $validated['status']
                    ?? 'active',
            ]);

            $rate->load([
                'deliveryMethod:id,name,slug,type,status',
                'country:id,name,iso2',
            ]);

            return $this->response->created(
                $rate,
                'Delivery method rate created successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to create delivery method rate.',
                500
            );
        }
    }

    /**
     * Show delivery method rate.
     */
    public function show(
        DeliveryMethodRate $deliveryMethodRate
    ): JsonResponse {
        try {

            $deliveryMethodRate->load([
                'deliveryMethod:id,name,slug,type,status',
                'country:id,name,iso2',
            ]);

            return $this->response->success(
                $deliveryMethodRate,
                'Delivery method rate retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve delivery method rate.',
                500
            );
        }
    }

    /**
     * Update delivery method rate.
     */
    public function update(
        Request $request,
        DeliveryMethodRate $deliveryMethodRate
    ): JsonResponse {
        $validated = $request->validate([

            'delivery_method_id' => [
                'required',
                'integer',
                'exists:delivery_methods,id',
            ],

            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'max:10',
            ],

            'estimated_days' => [
                'nullable',
                'integer',
                'min:1',
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

            /*
             * Prevent duplicate country rate.
             */
            $exists = DeliveryMethodRate::query()
                ->where(
                    'delivery_method_id',
                    $validated['delivery_method_id']
                )
                ->where(
                    'country_id',
                    $validated['country_id']
                )
                ->where(
                    'id',
                    '!=',
                    $deliveryMethodRate->id
                )
                ->exists();

            if ($exists) {

                return $this->response->error(
                    'A delivery rate for this country already exists for this delivery method.',
                    409
                );
            }

            $deliveryMethodRate->update([
                'delivery_method_id'
                    => $validated['delivery_method_id'],

                'country_id'
                    => $validated['country_id'],

                'price'
                    => $validated['price'],

                'currency'
                    => strtoupper($validated['currency']),

                'estimated_days'
                    => $validated['estimated_days']
                    ?? null,

                'status'
                    => $validated['status']
                    ?? $deliveryMethodRate->status,
            ]);

            $deliveryMethodRate->load([
                'deliveryMethod:id,name,slug,type,status',
                'country:id,name,iso2',
            ]);

            return $this->response->success(
                $deliveryMethodRate,
                'Delivery method rate updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update delivery method rate.',
                500
            );
        }
    }

    /**
     * Delete delivery method rate.
     */
    public function destroy(
        DeliveryMethodRate $deliveryMethodRate
    ): JsonResponse {
        try {

            $deliveryMethodRate->delete();

            return $this->response->noContent(
                'Delivery method rate deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to delete delivery method rate.',
                500
            );
        }
    }

    /**
     * Activate / deactivate rate.
     */
    public function statusChange(
        DeliveryMethodRate $deliveryMethodRate
    ): JsonResponse {
        try {

            $status = $deliveryMethodRate->status === 'active'
                ? 'inactive'
                : 'active';

            $deliveryMethodRate->update([
                'status' => $status,
            ]);

            return $this->response->success(
                $deliveryMethodRate->fresh()->load([
                    'deliveryMethod:id,name,slug,type,status',
                    'country:id,name,iso2',
                ]),
                $status === 'active'
                    ? 'Delivery rate activated successfully.'
                    : 'Delivery rate deactivated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update delivery rate status.',
                500
            );
        }
    }
}