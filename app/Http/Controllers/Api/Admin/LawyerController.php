<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LawyerProfile;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LawyerController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get lawyer list.
     *
     * Accessible by Super Admin and Administrator.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $query = LawyerProfile::query()
                ->with([
                    'user:id,name,email,phone,status',
                    'country:id,name',
                    'region:id,name',
                ])
                ->withCount('documents')
                ->withCount([
                    'documents as verified_documents_count' => function ($query) {
                        $query->where(
                            'verification_status',
                            'verified'
                        );
                    },
                ]);

            /*
             * Search.
             */
            if ($request->filled('search')) {

                $search = $request->input('search');

                $query->where(function ($query) use ($search) {

                    $query->where(
                        'professional_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'bar_registration_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'law_firm_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas('user', function ($query) use ($search) {

                        $query->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        );
                    });
                });
            }

            /*
             * Filter by approval status.
             */
            if ($request->filled('approval_status')) {

                $query->where(
                    'approval_status',
                    $request->input('approval_status')
                );
            }

            /*
             * Filter by country.
             */
            if ($request->filled('country_id')) {

                $query->where(
                    'country_id',
                    $request->input('country_id')
                );
            }

            /*
             * Filter by region.
             */
            if ($request->filled('region_id')) {

                $query->where(
                    'region_id',
                    $request->input('region_id')
                );
            }

            /*
             * Filter active/inactive lawyers.
             */
            if ($request->filled('is_active')) {

                $query->where(
                    'is_active',
                    filter_var(
                        $request->input('is_active'),
                        FILTER_VALIDATE_BOOLEAN
                    )
                );
            }

            /*
             * Default newest first.
             */
            $lawyers = $query
                ->latest('id')
                ->paginate(
                    $request->integer('per_page', 20)
                );

            return $this->response->success(
                $lawyers,
                'Lawyer list retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve lawyer list.',
                500
            );
        }
    }


    /**
     * Get complete lawyer details.
     */
    public function show(
        LawyerProfile $lawyer
    ): JsonResponse {
       
        try {

            $lawyer->load([
                'user:id,name,email,phone,status,email_verified_at,last_login_at',

                'country:id,name',

                'region:id,name',

                'documents',

                'serviceRegions.service:id,name,slug',

                'serviceRegions.country:id,name',

                'serviceRegions.region:id,name',

                'reviewedBy:id,name,email',
            ]);

            return $this->response->success(
                $lawyer,
                'Lawyer details retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve lawyer details.',
                500
            );
        }
    }


    /**
     * Approve lawyer.
     *
     * Lawyer can only be approved when all required
     * documents have been verified.
     */
    public function approve(
        Request $request,
        LawyerProfile $lawyer
    ): JsonResponse {
        try {

            /*
             * Already approved.
             */
            if ($lawyer->approval_status === 'approved') {

                return $this->response->error(
                    'This lawyer is already approved.',
                    409
                );
            }

            /*
             * Lawyer account must be active.
             */
            if (
                ! $lawyer->user ||
                $lawyer->user->status !== 'active'
            ) {
                return $this->response->error(
                    'The lawyer account is not active.',
                    422
                );
            }

            /*
             * Check documents.
             */
            $totalDocuments = $lawyer->documents()->count();

            if ($totalDocuments === 0) {

                return $this->response->error(
                    'The lawyer has not uploaded any documents.',
                    422
                );
            }

            $pendingDocuments = $lawyer->documents()
                ->where(
                    'verification_status',
                    'pending'
                )
                ->count();

            if ($pendingDocuments > 0) {

                return $this->response->error(
                    'All lawyer documents must be verified before approval.',
                    422,
                    [
                        'pending_documents' => $pendingDocuments,
                    ]
                );
            }

            $rejectedDocuments = $lawyer->documents()
                ->where(
                    'verification_status',
                    'rejected'
                )
                ->count();

            if ($rejectedDocuments > 0) {

                return $this->response->error(
                    'The lawyer has rejected documents. They must be replaced or verified before approval.',
                    422,
                    [
                        'rejected_documents' => $rejectedDocuments,
                    ]
                );
            }

            /*
             * Make sure every document is verified.
             */
            $unverifiedDocuments = $lawyer->documents()
                ->where(
                    'verification_status',
                    '!=',
                    'verified'
                )
                ->count();

            if ($unverifiedDocuments > 0) {

                return $this->response->error(
                    'All lawyer documents must be verified before approval.',
                    422
                );
            }

            DB::transaction(function () use (
                $request,
                $lawyer
            ) {

                $lawyer->update([
                    'approval_status' => 'approved',
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'approved_at' => now(),
                    'rejection_reason' => null,
                    'is_available' => true,
                    'is_active' => true,
                ]);
            });

            return $this->response->success(
                $lawyer->fresh()->load([
                    'user:id,name,email,phone,status',
                    'country:id,name',
                    'region:id,name',
                    'documents',
                    'serviceRegions.service:id,name,slug',
                    'serviceRegions.country:id,name',
                    'serviceRegions.region:id,name',
                    'reviewedBy:id,name,email',
                ]),
                'Lawyer approved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to approve lawyer.',
                500
            );
        }
    }


    /**
     * Reject lawyer.
     */
    public function reject(
        Request $request,
        LawyerProfile $lawyer
    ): JsonResponse {

        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        try {

            if ($lawyer->approval_status === 'approved') {

                return $this->response->error(
                    'An approved lawyer cannot be rejected directly.',
                    409
                );
            }

            DB::transaction(function () use (
                $request,
                $validated,
                $lawyer
            ) {

                $lawyer->update([
                    'approval_status' => 'rejected',
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'approved_at' => null,
                    'rejection_reason' => $validated['reason'],
                    'is_available' => false,
                ]);
            });

            return $this->response->success(
                $lawyer->fresh()->load([
                    'user:id,name,email,phone,status',
                    'country:id,name',
                    'region:id,name',
                    'documents',
                    'reviewedBy:id,name,email',
                ]),
                'Lawyer rejected successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to reject lawyer.',
                500
            );
        }
    }


    /**
     * Put lawyer under review.
     *
     * Useful when an approved lawyer changes important
     * profile information or service coverage.
     */
    public function review(
        Request $request,
        LawyerProfile $lawyer
    ): JsonResponse {
        try {

            $lawyer->update([
                'approval_status' => 'under_review',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'is_available' => false,
            ]);

            return $this->response->success(
                $lawyer->fresh()->load([
                    'user:id,name,email,phone,status',
                    'documents',
                    'reviewedBy:id,name,email',
                ]),
                'Lawyer moved to review successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to move lawyer to review.',
                500
            );
        }
    }


    /**
     * Enable/disable lawyer availability.
     *
     * This does not change approval status.
     */
    public function toggleAvailability(
        Request $request,
        LawyerProfile $lawyer
    ): JsonResponse {
        try {

            if ($lawyer->approval_status !== 'approved') {

                return $this->response->error(
                    'Only approved lawyers can change availability.',
                    422
                );
            }

            $lawyer->update([
                'is_available' => ! $lawyer->is_available,
            ]);

            return $this->response->success(
                $lawyer->fresh(),
                $lawyer->is_available
                    ? 'Lawyer is now available.'
                    : 'Lawyer is now unavailable.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to update lawyer availability.',
                500
            );
        }
    }
}
