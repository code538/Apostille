<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Region;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegionController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Display regions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Region::query()
            ->with('country:id,name,iso2');

        /*
         * Filter by country.
         */
        if ($request->filled('country_id')) {
            $query->where(
                'country_id',
                $request->country_id
            );
        }

        /*
         * Search by region name.
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

        $regions = $query
            ->orderBy('name')
            ->paginate(
                $request->integer('per_page', 50)
            );

        return $this->response->success(
            $regions,
            'Regions retrieved successfully.'
        );
    }

    /**
     * Store a new region.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        try {

            /*
             * Make sure the same region name
             * does not exist twice in one country.
             */
            $exists = Region::where(
                'country_id',
                $validated['country_id']
            )
                ->where(
                    'name',
                    $validated['name']
                )
                ->exists();

            if ($exists) {
                return $this->response->error(
                    'This region already exists for the selected country.',
                    422
                );
            }

            $region = Region::create([
                'country_id' => $validated['country_id'],
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $region->load(
                'country:id,name,iso2'
            );

            return $this->response->created(
                $region,
                'Region created successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Unable to create region.',
                500
            );
        }
    }

    /**
     * Display a specific region.
     */
    public function show(Region $region): JsonResponse
    {
        $region->load(
            'country:id,name,iso2'
        );

        return $this->response->success(
            $region,
            'Region retrieved successfully.'
        );
    }

    /**
     * Update region.
     */
    public function update(
        Request $request,
        Region $region
    ): JsonResponse {

        $validated = $request->validate([
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        try {

            $exists = Region::where(
                'country_id',
                $validated['country_id']
            )
                ->where(
                    'name',
                    $validated['name']
                )
                ->where(
                    'id',
                    '!=',
                    $region->id
                )
                ->exists();

            if ($exists) {
                return $this->response->error(
                    'This region already exists for the selected country.',
                    422
                );
            }

            $region->update([
                'country_id' => $validated['country_id'],
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'is_active' => $validated['is_active']
                    ?? $region->is_active,
            ]);

            $region->load(
                'country:id,name,iso2'
            );

            return $this->response->success(
                $region,
                'Region updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Unable to update region.',
                500
            );
        }
    }

    /**
     * Activate / deactivate region.
     */
    public function updateStatus(
        Request $request,
        Region $region
    ): JsonResponse {

        $validated = $request->validate([
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        try {

            $region->update([
                'is_active' => $validated['is_active'],
            ]);

            return $this->response->success(
                $region->fresh(),
                'Region status updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Unable to update region status.',
                500
            );
        }
    }

    /**
     * Delete/deactivate region.
     */
    public function destroy(Region $region): JsonResponse
    {
        try {

            /*
             * Prefer deactivation instead of deletion
             * because other records may reference this region.
             */
            $region->update([
                'is_active' => false,
            ]);

            return $this->response->noContent(
                'Region deactivated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Unable to deactivate region.',
                500
            );
        }
    }
}
