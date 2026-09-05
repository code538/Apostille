<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LawyerServicePricing;
use App\Models\LawyerServiceRegion;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LawyerServicePricingController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get lawyer service pricing list.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $query = LawyerServicePricing::query()
                ->with([
                    'lawyerServiceRegion.lawyerProfile.user:id,name,email',
                    'lawyerServiceRegion.service:id,name,slug',
                    'lawyerServiceRegion.country:id,name,iso2',
                    'lawyerServiceRegion.region:id,name,code',
                ]);

            /*
             * Filter by lawyer service region.
             */
            if ($request->filled('lawyer_service_region_id')) {
                $query->where(
                    'lawyer_service_region_id',
                    $request->integer('lawyer_service_region_id')
                );
            }

            /*
             * Filter by service level.
             */
            if ($request->filled('service_level')) {
                $query->where(
                    'service_level',
                    $request->input('service_level')
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

            $pricings = $query
                ->latest('id')
                ->paginate($perPage);

            return $this->response->success(
                $pricings,
                'Lawyer service pricing retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve lawyer service pricing.',
                500
            );
        }
    }

    /**
     * Create pricing for a lawyer service region.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'lawyer_service_region_id' => [
                'required',
                'integer',
                'exists:lawyer_service_regions,id',
            ],

            'service_level' => [
                'required',
                Rule::in([
                    'standard',
                    'express',
                    'urgent',
                ]),
            ],

            'fee' => [
                'required',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'estimated_days' => [
                'required',
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
             * Make sure the selected lawyer service
             * region exists.
             */
            $lawyerServiceRegion = LawyerServiceRegion::find(
                $validated['lawyer_service_region_id']
            );

            if (! $lawyerServiceRegion) {

                return $this->response->error(
                    'Lawyer service region not found.',
                    404
                );
            }

            /*
             * Prevent duplicate pricing for the
             * same service level.
             */
            $exists = LawyerServicePricing::query()
                ->where(
                    'lawyer_service_region_id',
                    $validated['lawyer_service_region_id']
                )
                ->where(
                    'service_level',
                    $validated['service_level']
                )
                ->exists();

            if ($exists) {

                return $this->response->error(
                    'Pricing for this service level already exists.',
                    409
                );
            }

            $pricing = LawyerServicePricing::create([
                'lawyer_service_region_id'
                    => $validated['lawyer_service_region_id'],

                'service_level'
                    => $validated['service_level'],

                'fee'
                    => $validated['fee'],

                'currency'
                    => strtoupper($validated['currency']),

                'estimated_days'
                    => $validated['estimated_days'],

                'status'
                    => $validated['status'] ?? 'active',
            ]);

            $pricing->load([
                'lawyerServiceRegion.lawyerProfile.user:id,name,email',
                'lawyerServiceRegion.service:id,name,slug',
                'lawyerServiceRegion.country:id,name,iso2',
                'lawyerServiceRegion.region:id,name,code',
            ]);

            return $this->response->created(
                $pricing,
                'Lawyer service pricing created successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to create lawyer service pricing.',
                500
            );
        }
    }

    /**
     * Show pricing details.
     */
    public function show(
        LawyerServicePricing $pricing
    ): JsonResponse {
        try {

            $pricing->load([
                'lawyerServiceRegion.lawyerProfile.user:id,name,email',
                'lawyerServiceRegion.service:id,name,slug',
                'lawyerServiceRegion.country:id,name,iso2',
                'lawyerServiceRegion.region:id,name,code',
            ]);

            return $this->response->success(
                $pricing,
                'Lawyer service pricing retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve lawyer service pricing.',
                500
            );
        }
    }

    /**
     * Update pricing.
     */
    public function update(
        Request $request,
        LawyerServicePricing $pricing
    ): JsonResponse {
        $validated = $request->validate([

            'service_level' => [
                'required',
                Rule::in([
                    'standard',
                    'express',
                    'urgent',
                ]),
            ],

            'fee' => [
                'required',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'estimated_days' => [
                'required',
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
             * Prevent duplicate service level.
             */
            $exists = LawyerServicePricing::query()
                ->where(
                    'lawyer_service_region_id',
                    $pricing->lawyer_service_region_id
                )
                ->where(
                    'service_level',
                    $validated['service_level']
                )
                ->where(
                    'id',
                    '!=',
                    $pricing->id
                )
                ->exists();

            if ($exists) {

                return $this->response->error(
                    'Pricing for this service level already exists.',
                    409
                );
            }

            $pricing->update([
                'service_level'
                    => $validated['service_level'],

                'fee'
                    => $validated['fee'],

                'currency'
                    => strtoupper($validated['currency']),

                'estimated_days'
                    => $validated['estimated_days'],

                'status'
                    => $validated['status']
                    ?? $pricing->status,
            ]);

            $pricing->load([
                'lawyerServiceRegion.lawyerProfile.user:id,name,email',
                'lawyerServiceRegion.service:id,name,slug',
                'lawyerServiceRegion.country:id,name,iso2',
                'lawyerServiceRegion.region:id,name,code',
            ]);

            return $this->response->success(
                $pricing,
                'Lawyer service pricing updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update lawyer service pricing.',
                500
            );
        }
    }

    /**
     * Delete pricing.
     */
    public function destroy(
        LawyerServicePricing $pricing
    ): JsonResponse {
        try {

            $pricing->delete();

            return $this->response->noContent(
                'Lawyer service pricing deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to delete lawyer service pricing.',
                500
            );
        }
    }

    /**
     * Activate/deactivate pricing.
     */
    public function statusChange(
        LawyerServicePricing $pricing
    ): JsonResponse {
        try {

            $status = $pricing->status === 'active'
                ? 'inactive'
                : 'active';

            $pricing->update([
                'status' => $status,
            ]);

            return $this->response->success(
                $pricing->fresh(),
                $status === 'active'
                    ? 'Pricing activated successfully.'
                    : 'Pricing deactivated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update pricing status.',
                500
            );
        }
    }
}