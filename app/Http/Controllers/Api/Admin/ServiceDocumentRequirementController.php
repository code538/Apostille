<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServiceDocumentRequirement;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceDocumentRequirementController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get document requirements.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $query = ServiceDocumentRequirement::query()
                ->with([
                    'service:id,name,slug',
                    'country:id,name,iso2',
                    'region:id,name,code',
                ]);

            /*
             * Filter by service.
             */
            if ($request->filled('service_id')) {
                $query->where(
                    'service_id',
                    $request->integer('service_id')
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
             * Filter by region.
             */
            if ($request->filled('region_id')) {
                $query->where(
                    'region_id',
                    $request->integer('region_id')
                );
            }

            /*
             * Filter by document type.
             */
            if ($request->filled('document_type')) {
                $query->where(
                    'document_type',
                    $request->input('document_type')
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

            $requirements = $query
                ->orderBy('service_id')
                ->orderBy('country_id')
                ->orderBy('region_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate($perPage);

            return $this->response->success(
                $requirements,
                'Document requirements retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve document requirements.',
                500
            );
        }
    }

    /**
     * Store a document requirement.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'service_id' => [
                'required',
                'integer',
                'exists:services,id',
            ],

            'country_id' => [
                'nullable',
                'integer',
                'exists:countries,id',
            ],

            'region_id' => [
                'nullable',
                'integer',
                'exists:regions,id',
            ],

            'document_type' => [
                'required',
                'string',
                'max:100',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_required' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
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
             * Validate region belongs to country.
             */
            if (
                ! empty($validated['region_id'])
                && empty($validated['country_id'])
            ) {
                return $this->response->error(
                    'Country is required when a region is selected.',
                    422
                );
            }

            if (
                ! empty($validated['region_id'])
                && ! empty($validated['country_id'])
            ) {

                $regionBelongsToCountry = Region::query()
                    ->where('id', $validated['region_id'])
                    ->where(
                        'country_id',
                        $validated['country_id']
                    )
                    ->exists();

                if (! $regionBelongsToCountry) {

                    return $this->response->error(
                        'Selected region does not belong to the selected country.',
                        422
                    );
                }
            }

            /*
             * Service must be active.
             */
            $service = Service::find(
                $validated['service_id']
            );

            if (! $service) {
                return $this->response->error(
                    'Service not found.',
                    404
                );
            }

            $requirement = DB::transaction(
                function () use ($validated) {

                    return ServiceDocumentRequirement::create([
                        'service_id' => $validated['service_id'],
                        'country_id' => $validated['country_id'] ?? null,
                        'region_id' => $validated['region_id'] ?? null,
                        'document_type' => $validated['document_type'],
                        'title' => $validated['title'],
                        'description' => $validated['description'] ?? null,
                        'is_required' => $validated['is_required'] ?? true,
                        'sort_order' => $validated['sort_order'] ?? 0,
                        'status' => $validated['status'] ?? 'active',
                    ]);
                }
            );

            $requirement->load([
                'service:id,name,slug',
                'country:id,name,iso2',
                'region:id,name,code',
            ]);

            return $this->response->created(
                $requirement,
                'Document requirement created successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to create document requirement.',
                500
            );
        }
    }

    /**
     * Get a single document requirement.
     */
    public function show(
        ServiceDocumentRequirement $requirement
    ): JsonResponse {
        try {

            $requirement->load([
                'service:id,name,slug',
                'country:id,name,iso2',
                'region:id,name,code',
            ]);

            return $this->response->success(
                $requirement,
                'Document requirement retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve document requirement.',
                500
            );
        }
    }

    /**
     * Update a document requirement.
     */
    public function update(
        Request $request,
        ServiceDocumentRequirement $requirement
    ): JsonResponse {
        
        $validated = $request->validate([

            'service_id' => [
                'required',
                'integer',
                'exists:services,id',
            ],

            'country_id' => [
                'nullable',
                'integer',
                'exists:countries,id',
            ],

            'region_id' => [
                'nullable',
                'integer',
                'exists:regions,id',
            ],

            'document_type' => [
                'required',
                'string',
                'max:100',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_required' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ]);
        //dd($validated);
        try {

            /*
             * Region requires country.
             */
            if (
                ! empty($validated['region_id'])
                && empty($validated['country_id'])
            ) {
                return $this->response->error(
                    'Country is required when a region is selected.',
                    422
                );
            }

            /*
             * Check region belongs to country.
             */
            if (
                ! empty($validated['region_id'])
                && ! empty($validated['country_id'])
            ) {

                $regionBelongsToCountry = Region::query()
                    ->where(
                        'id',
                        $validated['region_id']
                    )
                    ->where(
                        'country_id',
                        $validated['country_id']
                    )
                    ->exists();

                if (! $regionBelongsToCountry) {

                    return $this->response->error(
                        'Selected region does not belong to the selected country.',
                        422
                    );
                }
            }

            $requirement->update([
                'service_id' => $validated['service_id'],
                'country_id' => $validated['country_id'] ?? null,
                'region_id' => $validated['region_id'] ?? null,
                'document_type' => $validated['document_type'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_required' => $validated['is_required']
                    ?? $requirement->is_required,
                'sort_order' => $validated['sort_order']
                    ?? $requirement->sort_order,
                'status' => $validated['status']
                    ?? $requirement->status,
            ]);

            $requirement->load([
                'service:id,name,slug',
                'country:id,name,iso2',
                'region:id,name,code',
            ]);

            return $this->response->success(
                $requirement,
                'Document requirement updated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update document requirement.',
                500
            );
        }
    }

    /**
     * Delete a document requirement.
     */
    public function destroy(
        ServiceDocumentRequirement $requirement
    ): JsonResponse {
        try {

            $requirement->delete();

            return $this->response->noContent(
                'Document requirement deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to delete document requirement.',
                500
            );
        }
    }

    /**
     * Activate/deactivate requirement.
     */
    public function statusChange(
        ServiceDocumentRequirement $requirement
    ): JsonResponse {
        try {

            $status = $requirement->status === 'active'
                ? 'inactive'
                : 'active';

            $requirement->update([
                'status' => $status,
            ]);

            return $this->response->success(
                $requirement->fresh(),
                $status === 'active'
                    ? 'Document requirement activated successfully.'
                    : 'Document requirement deactivated successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update document requirement status.',
                500
            );
        }
    }
}