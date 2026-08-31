<?php

namespace App\Http\Controllers\Api\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\LawyerProfile;
use App\Models\LawyerServiceRegion;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LawyerServiceRegionController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get authenticated lawyer service coverage.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $user = $request->user();

            if (! $user->isApostilleOfficer()) {
                return $this->response->error(
                    'Only Apostille Officers can access service coverage.',
                    403
                );
            }

            $profile = LawyerProfile::where(
                'user_id',
                $user->id
            )->first();

            if (! $profile) {
                return $this->response->error(
                    'Lawyer profile not found.',
                    404
                );
            }

            $coverage = $profile->serviceRegions()
                ->with([
                    'service',
                    'country',
                    'region',
                ])
                ->latest('id')
                ->get();

            return $this->response->success(
                $coverage,
                'Service coverage retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve service coverage.',
                500
            );
        }
    }

    /**
     * Add service coverage.
     *
     * region_id = NULL means country-wide.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => [
                'required',
                'exists:services,id',
            ],

            'country_id' => [
                'required',
                'exists:countries,id',
            ],

            'region_id' => [
                'nullable',
                'exists:regions,id',
            ],

            'status' => [
                'nullable',
                'in:active,inactive',
            ],
        ]);

        try {

            $user = $request->user();

            if (! $user->isApostilleOfficer()) {
                return $this->response->error(
                    'Only Apostille Officers can manage service coverage.',
                    403
                );
            }

            $profile = LawyerProfile::where(
                'user_id',
                $user->id
            )->first();

            if (! $profile) {
                return $this->response->error(
                    'Please create your lawyer profile first.',
                    404
                );
            }

            /*
             * Make sure the selected region belongs
             * to the selected country.
             */
            if (! empty($validated['region_id'])) {

                $regionBelongsToCountry = DB::table('regions')
                    ->where('id', $validated['region_id'])
                    ->where('country_id', $validated['country_id'])
                    ->exists();

                if (! $regionBelongsToCountry) {
                    throw ValidationException::withMessages([
                        'region_id' => [
                            'The selected region does not belong to the selected country.',
                        ],
                    ]);
                }
            }

            /*
             * Prevent duplicate country-wide coverage.
             */
            $exists = LawyerServiceRegion::query()
                ->where('lawyer_profile_id', $profile->id)
                ->where('service_id', $validated['service_id'])
                ->where('country_id', $validated['country_id'])
                ->when(
                    isset($validated['region_id']),
                    function ($query) use ($validated) {
                        $query->where(
                            'region_id',
                            $validated['region_id']
                        );
                    },
                    function ($query) {
                        $query->whereNull('region_id');
                    }
                )
                ->exists();

            if ($exists) {
                return $this->response->error(
                    'This service coverage already exists.',
                    409
                );
            }

            $coverage = LawyerServiceRegion::create([
                'lawyer_profile_id' => $profile->id,
                'service_id' => $validated['service_id'],
                'country_id' => $validated['country_id'],
                'region_id' => $validated['region_id'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);

            /*
             * Adding coverage should not automatically approve
             * the lawyer. But if the lawyer is already approved,
             * an admin may need to review the new coverage.
             */
            if ($profile->approval_status === 'approved') {
                $profile->update([
                    'approval_status' => 'under_review',
                    'is_available' => false,
                ]);
            }

            return $this->response->created(
                $coverage->load([
                    'service',
                    'country',
                    'region',
                ]),
                'Service coverage added successfully.'
            );

        } catch (ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to add service coverage.',
                500
            );
        }
    }

    /**
     * Update service coverage.
     */
    public function update(
        Request $request,
        LawyerServiceRegion $serviceRegion
    ): JsonResponse {
        $validated = $request->validate([
            'service_id' => [
                'required',
                'exists:services,id',
            ],

            'country_id' => [
                'required',
                'exists:countries,id',
            ],

            'region_id' => [
                'nullable',
                'exists:regions,id',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        try {

            $user = $request->user();

            if (! $user->isApostilleOfficer()) {
                return $this->response->error(
                    'Only Apostille Officers can manage service coverage.',
                    403
                );
            }

            $profile = LawyerProfile::where(
                'user_id',
                $user->id
            )->first();

            if (
                ! $profile
                || $serviceRegion->lawyer_profile_id !== $profile->id
            ) {
                return $this->response->error(
                    'You are not authorized to update this service coverage.',
                    403
                );
            }

            /*
             * Validate region belongs to country.
             */
            if (! empty($validated['region_id'])) {

                $regionBelongsToCountry = DB::table('regions')
                    ->where('id', $validated['region_id'])
                    ->where('country_id', $validated['country_id'])
                    ->exists();

                if (! $regionBelongsToCountry) {
                    throw ValidationException::withMessages([
                        'region_id' => [
                            'The selected region does not belong to the selected country.',
                        ],
                    ]);
                }
            }

            /*
             * Check duplicate coverage excluding
             * current record.
             */
            $exists = LawyerServiceRegion::query()
                ->where('lawyer_profile_id', $profile->id)
                ->where('service_id', $validated['service_id'])
                ->where('country_id', $validated['country_id'])
                ->where('id', '!=', $serviceRegion->id)
                ->when(
                    isset($validated['region_id']),
                    function ($query) use ($validated) {
                        $query->where(
                            'region_id',
                            $validated['region_id']
                        );
                    },
                    function ($query) {
                        $query->whereNull('region_id');
                    }
                )
                ->exists();

            if ($exists) {
                return $this->response->error(
                    'This service coverage already exists.',
                    409
                );
            }

            $serviceRegion->update([
                'service_id' => $validated['service_id'],
                'country_id' => $validated['country_id'],
                'region_id' => $validated['region_id'] ?? null,
                'status' => $validated['status'],
            ]);

            /*
             * Coverage changes require review again.
             */
            if ($profile->approval_status === 'approved') {
                $profile->update([
                    'approval_status' => 'under_review',
                    'is_available' => false,
                ]);
            }

            return $this->response->success(
                $serviceRegion->fresh()->load([
                    'service',
                    'country',
                    'region',
                ]),
                'Service coverage updated successfully.'
            );

        } catch (ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update service coverage.',
                500
            );
        }
    }

    /**
     * Delete service coverage.
     */
    public function destroy(
        Request $request,
        LawyerServiceRegion $serviceRegion
    ): JsonResponse {
        try {

            $user = $request->user();

            if (! $user->isApostilleOfficer()) {
                return $this->response->error(
                    'Only Apostille Officers can manage service coverage.',
                    403
                );
            }

            $profile = LawyerProfile::where(
                'user_id',
                $user->id
            )->first();

            if (
                ! $profile
                || $serviceRegion->lawyer_profile_id !== $profile->id
            ) {
                return $this->response->error(
                    'You are not authorized to delete this service coverage.',
                    403
                );
            }

            $serviceRegion->delete();

            return $this->response->noContent(
                'Service coverage deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to delete service coverage.',
                500
            );
        }
    }
}


