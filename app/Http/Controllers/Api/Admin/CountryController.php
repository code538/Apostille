<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Display all countries.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Country::query();

        /*
         * Search by country name.
         */
        if ($request->filled('search')) {
            $query->where(
                'name',
                'like',
                '%' . $request->search . '%'
            );
        }

        /*
         * Filter by status.
         */
        if ($request->has('is_active')) {
            $query->where(
                'is_active',
                $request->boolean('is_active')
            );
        }

        $countries = $query
            ->orderBy('name')
            ->paginate(
                $request->integer('per_page', 50)
            );

        return $this->response->success(
            $countries,
            'Countries retrieved successfully.'
        );
    }

    /**
     * Store a new country.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'iso2' => [
                'required',
                'string',
                'size:2',
                'unique:countries,iso2',
            ],

            'iso3' => [
                'required',
                'string',
                'size:3',
                'unique:countries,iso3',
            ],

            'phone_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'currency_code' => [
                'nullable',
                'string',
                'size:3',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        try {

            $country = Country::create([
                'name' => $validated['name'],
                'iso2' => strtoupper($validated['iso2']),
                'iso3' => strtoupper($validated['iso3']),
                'phone_code' => $validated['phone_code'] ?? null,
                'currency_code' => isset($validated['currency_code'])
                    ? strtoupper($validated['currency_code'])
                    : null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return $this->response->created(
                $country,
                'Country created successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Unable to create country.',
                500
            );
        }
    }

    /**
     * Display a specific country.
     */
    public function show(Country $country): JsonResponse
    {
        $country->load([
            'regions' => function ($query) {
                $query->orderBy('name');
            },
        ]);

        return $this->response->success(
            $country,
            'Country retrieved successfully.'
        );
    }

    /**
     * Update country.
     */
    public function update(
        Request $request,
        Country $country
    ): JsonResponse {

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'iso2' => [
                'required',
                'string',
                'size:2',
                Rule::unique('countries', 'iso2')
                    ->ignore($country->id),
            ],

            'iso3' => [
                'required',
                'string',
                'size:3',
                Rule::unique('countries', 'iso3')
                    ->ignore($country->id),
            ],

            'phone_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'currency_code' => [
                'nullable',
                'string',
                'size:3',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        try {

            $country->update([
                'name' => $validated['name'],
                'iso2' => strtoupper($validated['iso2']),
                'iso3' => strtoupper($validated['iso3']),
                'phone_code' => $validated['phone_code'] ?? null,
                'currency_code' => isset($validated['currency_code'])
                    ? strtoupper($validated['currency_code'])
                    : null,
                'is_active' => $validated['is_active']
                    ?? $country->is_active,
            ]);

            return $this->response->success(
                $country->fresh(),
                'Country updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Unable to update country.',
                500
            );
        }
    }

    /**
     * Activate / deactivate country.
     */
    public function updateStatus(
        Request $request,
        Country $country
    ): JsonResponse {

        $validated = $request->validate([
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        try {

            $country->update([
                'is_active' => $validated['is_active'],
            ]);

            return $this->response->success(
                $country->fresh(),
                'Country status updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Unable to update country status.',
                500
            );
        }
    }

    /**
     * Delete country.
     */
    public function destroy(Country $country): JsonResponse
    {
        try {

            /*
             * Because regions and other records may
             * depend on this country, prefer deactivation
             * instead of physical deletion.
             */
            $country->update([
                'is_active' => false,
            ]);

            return $this->response->noContent(
                'Country deactivated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Unable to deactivate country.',
                500
            );
        }
    }
}
