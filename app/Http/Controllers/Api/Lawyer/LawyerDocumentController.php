<?php

namespace App\Http\Controllers\Api\Lawyer;

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
     * Get authenticated lawyer documents.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $user = $request->user();

            if (! $user->isApostilleOfficer()) {
                return $this->response->error(
                    'Only Apostille Officers can access lawyer documents.',
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

            $documents = $profile->documents()
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
     * Upload a lawyer document.
     */
    public function store(Request $request): JsonResponse
    { 
        $validated = $request->validate([
            'document_type' => [
                'required',
                Rule::in([
                    'bar_certificate',
                    'practising_certificate',
                    'professional_license',
                    'government_id',
                    'passport',
                    'proof_of_address',
                    'law_degree',
                    'law_firm_registration',
                    'other',
                ]),
            ],

            'document_number' => [
                'nullable',
                'string',
                'max:150',
            ],

            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:10240',
            ],

            'expires_at' => [
                'nullable',
                'date',
            ],

            'reviewer_notes' => [
                'nullable',
                'string',
            ],
        ]);

        try {

            $user = $request->user();
            //dd($user);
            if (! $user->isApostilleOfficer()) {
                return $this->response->error(
                    'Only Apostille Officers can upload lawyer documents.',
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
            //dd($profile);
            $file = $request->file('document');
            
            $path = $file->store(
                'lawyer-documents/' . $profile->id,
                'private'
            );
            
            $document = LawyerDocument::create([
                'lawyer_profile_id' => $profile->id,
                'document_type' => $validated['document_type'],
                'document_number' => $validated['document_number'] ?? null,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'verification_status' => 'pending',
                'expires_at' => $validated['expires_at'] ?? null,
                'reviewer_notes' => $validated['reviewer_notes'] ?? null,
            ]);

            /*
             * If a lawyer uploads/replaces documents,
             * profile should remain unavailable until
             * the documents are reviewed.
             */
            $profile->update([
                'approval_status' => 'under_review',
                'is_available' => false,
            ]);

            return $this->response->created(
                $document,
                'Document uploaded successfully and is pending verification.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to upload lawyer document.',
                500
            );
            
        }
    }

    /**
     * Delete a lawyer document.
     */
    public function destroy(
        Request $request,
        LawyerDocument $document
    ): JsonResponse {
        try {

            $user = $request->user();

            if (! $user->isApostilleOfficer()) {
                return $this->response->error(
                    'Only Apostille Officers can delete lawyer documents.',
                    403
                );
            }

            $profile = LawyerProfile::where(
                'user_id',
                $user->id
            )->first();

            if (
                ! $profile
                || $document->lawyer_profile_id !== $profile->id
            ) {
                return $this->response->error(
                    'You are not authorized to delete this document.',
                    403
                );
            }

            /*
             * Delete physical file.
             */
            if (
                $document->file_path
                && Storage::disk('private')->exists(
                    $document->file_path
                )
            ) {
                Storage::disk('private')->delete(
                    $document->file_path
                );
            }

            $document->delete();

            /*
             * Profile needs review again.
             */
            $profile->update([
                'approval_status' => 'pending',
                'is_available' => false,
            ]);

            return $this->response->noContent(
                'Document deleted successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to delete lawyer document.',
                500
            );
        }
    }
}


