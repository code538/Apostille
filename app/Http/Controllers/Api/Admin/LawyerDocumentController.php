<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LawyerDocument;
use App\Models\LawyerProfile;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LawyerDocumentController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }


    /**
     * Get documents for a lawyer.
     */
    public function index(
        LawyerProfile $lawyer
    ): JsonResponse {
        try {

            $documents = $lawyer->documents()
                ->latest('id')
                ->get();

            return $this->response->success(
                $documents,
                'Lawyer documents retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve lawyer documents.',
                500
            );
        }
    }


    /**
     * View/download a lawyer document.
     *
     * The file remains private.
     */
    public function view(
        LawyerProfile $lawyer,
        LawyerDocument $document
    ) {
        // dd(
        //     $lawyer,
        //     $document,
        //     $lawyer->id,
        //     $document->lawyer_profile_id
        // );
        try {

            /*
             * Make sure document belongs to this lawyer.
             */
            if (
                $document->lawyer_profile_id != $lawyer->id
            ) {
                return $this->response->error(
                    'Document does not belong to this lawyer.',
                    404
                );
            }

            if (
                ! $document->file_path
                || ! Storage::disk('local')->exists(
                    $document->file_path
                )
            ) {
                return $this->response->error(
                    'Document file not found.',
                    404
                );
            }

            return Storage::disk('local')->response(
                $document->file_path,
                $document->file_name,
                [
                    'Content-Type' => $document->mime_type,
                    'Content-Disposition' => 'inline; filename="' .
                        $document->file_name .
                        '"',
                ]
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to view document.',
                500
            );
        }
    }


    /**
     * Verify a lawyer document.
     */
    public function verify(
        Request $request,
        LawyerProfile $lawyer,
        LawyerDocument $document
    ): JsonResponse {
        try {

            /*
             * Make sure document belongs to lawyer.
             */
            if (
                $document->lawyer_profile_id !== $lawyer->id
            ) {
                return $this->response->error(
                    'Document does not belong to this lawyer.',
                    404
                );
            }

            $document->update([
                'verification_status' => 'verified',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'reviewer_notes' => $request->input(
                    'notes'
                ),
            ]);

            /*
             * If all documents are verified,
             * lawyer can move to under_review.
             */
            $hasPendingDocuments = $lawyer->documents()
                ->where(
                    'verification_status',
                    'pending'
                )
                ->exists();

            $hasRejectedDocuments = $lawyer->documents()
                ->where(
                    'verification_status',
                    'rejected'
                )
                ->exists();

            if (
                ! $hasPendingDocuments
                && ! $hasRejectedDocuments
            ) {
                $lawyer->update([
                    'approval_status' => 'under_review',
                    'is_available' => false,
                ]);
            }

            return $this->response->success(
                $document->fresh()->load(
                    'reviewedBy:id,name,email'
                ),
                'Document verified successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to verify document.',
                500
            );

            // report($e);

            // return $this->response->error(
            //     $e->getMessage(),
            //     500,
            //     [
            //         'file' => $e->getFile(),
            //         'line' => $e->getLine(),
            //     ]
            // );
        }
    }


    /**
     * Reject a lawyer document.
     */
    public function reject(
        Request $request,
        LawyerProfile $lawyer,
        LawyerDocument $document
    ): JsonResponse {

        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        try {

            if (
                $document->lawyer_profile_id !== $lawyer->id
            ) {
                return $this->response->error(
                    'Document does not belong to this lawyer.',
                    404
                );
            }

            $document->update([
                'verification_status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'reviewer_notes' => $validated['reason'],
            ]);

            /*
             * A rejected document means lawyer cannot
             * currently be available.
             */
            $lawyer->update([
                'approval_status' => 'rejected',
                'is_available' => false,
                'rejection_reason' => $validated['reason'],
            ]);

            return $this->response->success(
                $document->fresh()->load(
                    'reviewedBy:id,name,email'
                ),
                'Document rejected successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to reject document.',
                500
            );
        }
    }
}
