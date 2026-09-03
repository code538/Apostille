<?php

namespace App\Http\Controllers\Api\Lawyer;

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
     * Get logged-in lawyer's pricing.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $user = $request->user();

            $profile = $user->lawyerProfile;

            if (! $profile) {

                return $this->response->error(
                    'Please create your lawyer profile first.',
                    404
                );
            }

            $query = LawyerServicePricing::query()
                ->whereHas(
                    'lawyerServiceRegion',
                    function ($query) use ($profile) {

                        $query->where(
                            'lawyer_profile_id',
                            $profile->id
                        );
                    }
                )
                ->with([
                    'lawyerServiceRegion.service:id,name,slug',
                    'lawyerServiceRegion.country:id,name,iso2',
                    'lawyerServiceRegion.region:id,name,code',
                ]);

            if ($request->filled('service_level')) {

                $query->where(
                    'service_level',
                    $request->input('service_level')
                );
            }

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
                'Your service pricing retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve your service pricing.',
                500
            );
        }
    }

    /**
     * Create pricing.
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

            $user = $request->user();

            $profile = $user->lawyerProfile;

            if (! $profile) {

                return $this->response->error(
                    'Please create your lawyer profile first.',
                    404
                );
            }

            /*
             * IMPORTANT:
             * Make sure the selected service region
             * belongs to the logged-in lawyer.
             */
            $lawyerServiceRegion =
                LawyerServiceRegion::query()
                    ->where(
                        'id',
                        $validated['lawyer_service_region_id']
                    )
                    ->where(
                        'lawyer_profile_id',
                        $profile->id
                    )
                    ->first();

            if (! $lawyerServiceRegion) {

                return $this->response->error(
                    'You are not authorized to manage this service region.',
                    403
                );
            }

            /*
             * Prevent duplicate service level.
             */
            $exists = LawyerServicePricing::query()
                ->where(
                    'lawyer_service_region_id',
                    $lawyerServiceRegion->id
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
                    => $lawyerServiceRegion->id,

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
                'lawyerServiceRegion.service:id,name,slug',
                'lawyerServiceRegion.country:id,name,iso2',
                'lawyerServiceRegion.region:id,name,code',
            ]);

            return $this->response->created(
                $pricing,
                'Service pricing created successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to create service pricing.',
                500
            );
        }
    }

    /**
     * Update own pricing.
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

            $user = $request->user();

            $profile = $user->lawyerProfile;

            if (! $profile) {

                return $this->response->error(
                    'Lawyer profile not found.',
                    404
                );
            }

            /*
             * Verify ownership.
             */
            $belongsToLawyer = $pricing
                ->lawyerServiceRegion()
                ->where(
                    'lawyer_profile_id',
                    $profile->id
                )
                ->exists();

            if (! $belongsToLawyer) {

                return $this->response->error(
                    'You are not authorized to update this pricing.',
                    403
                );
            }

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
                'lawyerServiceRegion.service:id,name,slug',
                'lawyerServiceRegion.country:id,name,iso2',
                'lawyerServiceRegion.region:id,name,code',
            ]);

            return $this->response->success(
                $pricing,
                'Service pricing updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update service pricing.',
                500
            );
        }
    }

    /**
     * Delete own pricing.
     */
    public function destroy(
        Request $request,
        LawyerServicePricing $pricing
    ): JsonResponse {
        try {

            $profile = $request->user()->lawyerProfile;

            if (! $profile) {

                return $this->response->error(
                    'Lawyer profile not found.',
                    404
                );
            }

            $belongsToLawyer = $pricing
                ->lawyerServiceRegion()
                ->where(
                    'lawyer_profile_id',
                    $profile->id
                )
                ->exists();

            if (! $belongsToLawyer) {

                return $this->response->error(
                    'You are not authorized to delete this pricing.',
                    403
                );
            }

            $pricing->delete();

            return $this->response->noContent(
                'Service pricing deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to delete service pricing.',
                500
            );
        }
    }
}