<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get active countries.
     */
    public function countries(Request $request): JsonResponse
    {
        try {

            $countries = Country::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'iso2',
                ]);

            return $this->response->success(
                $countries,
                'Countries retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve countries.',
                500
            );
        }
    }

    /**
     * Get regions belonging to a country.
     */
    public function regions(
        Request $request,
        Country $country
    ): JsonResponse {

        try {

            if ($country->is_active !== true) {

                return $this->response->error(
                    'Country is not available.',
                    404
                );
            }

            $regions = $country->regions()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'country_id',
                    'name',
                    'code',
                ]);

            return $this->response->success(
                $regions,
                'Regions retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve regions.',
                500
            );
        }
    }
}