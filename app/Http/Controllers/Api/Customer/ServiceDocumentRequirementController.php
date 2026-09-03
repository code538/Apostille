<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServiceDocumentRequirement;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceDocumentRequirementController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get applicable document requirements.
     *
     * Customer selects:
     * service + country + optional region.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'service_id' => [
                'required',
                'integer',
                'exists:services,id',
            ],

            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
            ],

            'region_id' => [
                'nullable',
                'integer',
                'exists:regions,id',
            ],
        ]);
        //dd($validated);
        try {

            /*
             * Make sure service is active.
             */
            $service = Service::query()
                ->where('id', $validated['service_id'])
                ->where('status', 'active')
                ->first();

            if (! $service) {

                return $this->response->error(
                    'Service is not available.',
                    404
                );
            }

            /*
             * If region is selected,
             * make sure it belongs to selected country.
             */
            if (! empty($validated['region_id'])) {

                $validRegion = Region::query()
                    ->where(
                        'id',
                        $validated['region_id']
                    )
                    ->where(
                        'country_id',
                        $validated['country_id']
                    )
                    ->exists();

                if (! $validRegion) {

                    return $this->response->error(
                        'Selected region does not belong to the selected country.',
                        422
                    );
                }
            }

            /*
             * Get applicable requirements.
             *
             * Priority:
             *
             * 1. Global
             * 2. Country-wide
             * 3. Region-specific
             */
            $query = ServiceDocumentRequirement::query()
                ->where(
                    'service_id',
                    $validated['service_id']
                )
                ->where(
                    'status',
                    'active'
                )
                ->where(function ($query) use ($validated) {

                    /*
                     * Global requirement.
                     */
                    $query->where(function ($query) {

                        $query
                            ->whereNull('country_id')
                            ->whereNull('region_id');

                    });

                    /*
                     * Country-wide requirement.
                     */
                    $query->orWhere(function ($query) use ($validated) {

                        $query
                            ->where(
                                'country_id',
                                $validated['country_id']
                            )
                            ->whereNull('region_id');

                    });

                    /*
                     * Region-specific requirement.
                     */
                    if (! empty($validated['region_id'])) {

                        $query->orWhere(function ($query) use ($validated) {

                            $query
                                ->where(
                                    'country_id',
                                    $validated['country_id']
                                )
                                ->where(
                                    'region_id',
                                    $validated['region_id']
                                );
                        });
                    }
                });

            $requirements = $query
                ->with([
                    'service:id,name,slug',
                    'country:id,name,iso2',
                    'region:id,name,code',
                ])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            return $this->response->success(
                $requirements,
                'Applicable document requirements retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve document requirements.',
                500
            );
        }
    }
}