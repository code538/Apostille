<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\LawyerServiceRegion;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LawyerController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get available lawyers for selected
     * service, country and region.
     */
    // public function index(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'service_id' => [
    //             'required',
    //             'integer',
    //             'exists:services,id',
    //         ],

    //         'country_id' => [
    //             'required',
    //             'integer',
    //             'exists:countries,id',
    //         ],

    //         'region_id' => [
    //             'nullable',
    //             'integer',
    //             'exists:regions,id',
    //         ],
    //     ]);
    //     //dd('okk');
    //     try {

    //         $query = LawyerServiceRegion::query()
    //             ->where(
    //                 'lawyer_service_regions.service_id',
    //                 $validated['service_id']
    //             )
    //             ->where(
    //                 'lawyer_service_regions.country_id',
    //                 $validated['country_id']
    //             )
    //             ->where(
    //                 'lawyer_service_regions.status',
    //                 'active'
    //             )
    //             ->whereHas('service', function ($query) {
    //                 $query->where('status', 'active');
    //             })
    //             ->whereHas('lawyer', function ($query) {

    //                 $query
    //                     ->where(
    //                         'approval_status',
    //                         'approved'
    //                     )
    //                     ->where(
    //                         'is_available',
    //                         true
    //                     )
    //                     ->whereHas('user', function ($query) {
    //                         $query->where(
    //                             'status',
    //                             'active'
    //                         );
    //                     });
    //             });

    //         /*
    //          * Region is optional.
    //          *
    //          * If region is selected:
    //          * return lawyers specifically serving
    //          * that region OR lawyers serving the
    //          * whole country (region_id = NULL).
    //          */
    //         if (! empty($validated['region_id'])) {

    //             $query->where(function ($query) use ($validated) {

    //                 $query
    //                     ->where(
    //                         'region_id',
    //                         $validated['region_id']
    //                     )
    //                     ->orWhereNull('region_id');

    //             });
    //         }

    //         $lawyers = $query
    //             ->with([
    //                 'lawyer:id,user_id,full_name,photo,approval_status,is_available',
    //                 'lawyer.user:id,name,email',
    //                 'service:id,name,slug',
    //                 'country:id,name',
    //                 'region:id,name',
    //                 'pricings' => function ($query) {
    //                     $query->where(
    //                         'status',
    //                         'active'
    //                     );
    //                 },
    //             ])
    //             ->paginate(
    //                 min(
    //                     (int) $request->input(
    //                         'per_page',
    //                         15
    //                     ),
    //                     100
    //                 )
    //             );

    //         return $this->response->success(
    //             $lawyers,
    //             'Available lawyers retrieved successfully.'
    //         );

    //     } catch (\Throwable $e) {

    //         report($e);

    //         return $this->response->error(
    //             'Failed to retrieve available lawyers.',
    //             500
    //         );
    //     }
    // }

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

        try {

            $serviceId = $validated['service_id'];
            $countryId = $validated['country_id'];
            $regionId = $validated['region_id'] ?? null;

            $query = LawyerServiceRegion::query()
                ->where(
                    'lawyer_service_regions.service_id',
                    $serviceId
                )
                ->where(
                    'lawyer_service_regions.country_id',
                    $countryId
                )
                ->where(
                    'lawyer_service_regions.status',
                    'active'
                )

                // Service must be active
                ->whereHas('service', function ($query) {
                    $query->where('status', 'active');
                })

                // Lawyer profile must be approved and available
                ->whereHas('lawyerProfile', function ($query) {

                    $query->where(
                        'approval_status',
                        'approved'
                    )
                    ->where(
                        'is_available',
                        true
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->whereHas('user', function ($query) {
                        $query->where(
                            'status',
                            'active'
                        );
                    });
                });

            /*
            |--------------------------------------------------------------------------
            | Region filtering
            |--------------------------------------------------------------------------
            |
            | If region is selected:
            |
            | 1. Lawyer specifically serving this region
            | OR
            | 2. Lawyer serving the entire country (region_id = NULL)
            |
            */

            if ($regionId !== null) {

                $query->where(function ($query) use ($regionId) {

                    $query->where(
                        'region_id',
                        $regionId
                    )
                    ->orWhereNull('region_id');

                });
            }

            $lawyers = $query
                ->with([
                    'lawyerProfile:id,user_id,professional_name,profile_photo,approval_status,is_available,is_active',

                    'lawyerProfile.user:id,name,email',

                    'service:id,name,slug',

                    'country:id,name',

                    'region:id,name',

                    'pricings' => function ($query) {
                        $query->where(
                            'status',
                            'active'
                        );
                    },
                ])
                ->paginate(
                    min(
                        (int) $request->input('per_page', 15),
                        100
                    )
                );

            return $this->response->success(
                $lawyers,
                'Available lawyers retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve available lawyers.',
                500
            );
        }
    }
}