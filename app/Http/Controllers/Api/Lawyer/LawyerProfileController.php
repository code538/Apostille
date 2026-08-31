<?php

namespace App\Http\Controllers\Api\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\LawyerProfile;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LawyerProfileController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get authenticated lawyer profile.
     */
    public function me(Request $request): JsonResponse
    {  
        try {
            $user = $request->user();

            if (! $user->isApostilleOfficer()) {
                return $this->response->error(
                    'Only Apostille Officers can access this profile.',
                    403
                );
            }

            $profile = LawyerProfile::with([
                'user',
                'country',
                'region',
                'documents',
                'serviceRegions.service',
                'serviceRegions.country',
                'serviceRegions.region',
            ])
                ->where('user_id', $user->id)
                ->first();

            if (! $profile) {
                return $this->response->error(
                    'Lawyer profile not found.',
                    404
                );
            }

            return $this->response->success(
                $profile,
                'Lawyer profile retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve lawyer profile.',
                500
            );
        }
    }

    /**
     * Create or update authenticated lawyer profile.
     */
    public function storeOrUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'professional_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'bar_registration_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'bar_council_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'law_firm_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'professional_bio' => [
                'nullable',
                'string',
            ],

            'country_id' => [
                'nullable',
                'exists:countries,id',
            ],

            'region_id' => [
                'nullable',
                'exists:regions,id',
            ],

            'address_line_1' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address_line_2' => [
                'nullable',
                'string',
                'max:255',
            ],

            'city' => [
                'nullable',
                'string',
                'max:150',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:30',
            ],

            'years_of_experience' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        try {

            $user = $request->user();
            //dd($user);
            if (! $user->isApostilleOfficer()) {
                return $this->response->error(
                    'Only Apostille Officers can update a lawyer profile.',
                    403
                );
            }

            $profile = DB::transaction(function () use (
                $request,
                $validated,
                $user
            ) {

                $profile = LawyerProfile::firstOrNew([
                    'user_id' => $user->id,
                ]);

                /*
                 * Upload profile photo.
                 */
                if ($request->hasFile('profile_photo')) {

                    /*
                     * Delete old photo.
                     */
                    if (
                        $profile->profile_photo
                        && Storage::disk('public')->exists(
                            $profile->profile_photo
                        )
                    ) {
                        Storage::disk('public')->delete(
                            $profile->profile_photo
                        );
                    }

                    $validated['profile_photo'] = $request
                        ->file('profile_photo')
                        ->store(
                            'lawyer-profiles/' . $user->id,
                            'public'
                        );
                }

                /*
                 * New/updated profile must go through
                 * approval again.
                 */
                if ($profile->exists) {
                    $validated['approval_status'] = 'pending';
                    $validated['reviewed_by'] = null;
                    $validated['reviewed_at'] = null;
                    $validated['approved_at'] = null;
                    $validated['rejection_reason'] = null;
                    $validated['is_available'] = false;
                } else {
                    $validated['approval_status'] = 'pending';
                    $validated['is_available'] = false;
                    $validated['is_active'] = true;
                }

                $profile->fill($validated);
                $profile->save();

                return $profile;
            });

            return $this->response->success(
                $profile->load([
                    'user',
                    'country',
                    'region',
                    'documents',
                    'serviceRegions.service',
                    'serviceRegions.country',
                    'serviceRegions.region',
                ]),
                'Lawyer profile saved successfully. Your profile is pending approval.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to save lawyer profile.',
                500
            );
        }
    }
}

