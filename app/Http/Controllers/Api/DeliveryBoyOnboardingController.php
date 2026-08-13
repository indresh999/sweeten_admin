<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryBoy;
use App\Models\DeliveryDocument;
use App\Mail\DeliveryDocumentsSubmittedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DeliveryBoyOnboardingController extends Controller
{
    private array $requiredDocs = [
        'aadhar', 'pan', 'driving_license',
        'vehicle_rc_front', 'vehicle_rc_back',
        'bank_passbook', 'selfie',
    ];

    // ── STEP 0 — Send OTP (handled by DeliveryBoyAuthController) ────

    // ── STEP 0 — Verify OTP (handled by DeliveryBoyAuthController) ──

    // =========================================================================
    // STEP 1 — Fill Personal Details
    // PUT /delivery/onboarding/details
    // =========================================================================
    public function updateDetails(Request $request): JsonResponse
    {
        $boy = $request->user();

        $validator = Validator::make($request->all(), [
            'full_name'        => 'required|string|max:100',
            'phone_number'     => [
                'required', 'string', 'regex:/^[6-9]\d{9}$/',
                \Illuminate\Validation\Rule::unique('delivery_boys', 'phone_number')->ignore($boy->id),
            ],
            'vehicle_type'     => 'required|in:bike,scooter,cycle,car,bicycle,walking',
            'working_city'     => 'required|string|max:100',
            'working_city_lat' => 'nullable|numeric|between:-90,90',
            'working_city_lng' => 'nullable|numeric|between:-180,180',
        ], [
            'phone_number.regex'   => 'Enter a valid 10-digit Indian mobile number.',
            'phone_number.unique'  => 'This mobile number is already registered with another account.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boy->update(array_merge(
            $validator->validated(),
            ['onboarding_step' => max($boy->onboarding_step, 1)]
        ));

        return response()->json([
            'status'          => true,
            'message'         => 'Personal details saved.',
            'onboarding_step' => $boy->fresh()->onboarding_step,
            'data'            => $this->boySummary($boy->fresh()),
        ]);
    }

    // =========================================================================
    // STEP 2 — Upload Profile Photo
    // POST /delivery/onboarding/photo
    // =========================================================================
    public function uploadPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boy = $request->user();

        if ($boy->picture && Storage::disk('public')->exists($boy->picture)) {
            Storage::disk('public')->delete($boy->picture);
        }

        $path = $request->file('photo')->store('delivery_boys/' . $boy->id, 'public');
        $boy->update([
            'picture'          => $path,
            'onboarding_step'  => max($boy->onboarding_step, 2),
        ]);

        return response()->json([
            'status'          => true,
            'message'         => 'Profile photo uploaded.',
            'onboarding_step' => $boy->fresh()->onboarding_step,
            'data'            => ['picture_url' => asset('storage/' . $path)],
        ]);
    }

    // =========================================================================
    // STEP 3 — Upload Documents (one at a time)
    // POST /delivery/onboarding/document
    // Body: doc_type + file (multipart)
    // =========================================================================
    public function uploadDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'doc_type' => 'required|in:' . implode(',', $this->requiredDocs),
            'file'     => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boy = $request->user();

        // Delete old file if re-uploading
        $existing = DeliveryDocument::where('delivery_boy_id', $boy->id)
            ->where('doc_type', $request->doc_type)
            ->first();

        if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
            Storage::disk('public')->delete($existing->file_path);
        }

        $path = $request->file('file')->store("delivery_documents/{$boy->id}", 'public');

        $doc = DeliveryDocument::updateOrCreate(
            ['delivery_boy_id' => $boy->id, 'doc_type' => $request->doc_type],
            [
                'file_path'   => $path,
                'status'      => 'pending',
                'uploaded_at' => now(),
            ]
        );

        // Update onboarding step based on docs uploaded
        $uploadedTypes = $boy->documents()->pluck('doc_type')->toArray();
        $uploadedCount = count(array_intersect($this->requiredDocs, $uploadedTypes));

        if ($uploadedCount >= 7 && $boy->onboarding_step < 4) {
            $boy->update(['onboarding_step' => 4]);
        } elseif ($uploadedCount > 0 && $boy->onboarding_step < 3) {
            $boy->update(['onboarding_step' => 3]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Document uploaded.',
            'data'    => [
                'id'        => $doc->id,
                'doc_type'  => $doc->doc_type,
                'file_url'  => asset('storage/' . $doc->file_path),
                'status'    => $doc->status,
            ],
            'progress' => [
                'uploaded'     => $uploadedCount,
                'total'        => count($this->requiredDocs),
                'all_uploaded' => $uploadedCount >= 7,
            ],
        ], 201);
    }

    // =========================================================================
    // STEP 4 — Submit for Review
    // POST /delivery/onboarding/submit
    // =========================================================================
    public function submitForReview(Request $request): JsonResponse
    {
        $boy = $request->user();

        if ($boy->status === 'pending' || $boy->is_verified) {
            return response()->json([
                'status'  => false,
                'message' => $boy->is_verified
                    ? 'Your account is already verified.'
                    : 'Your application is already under review.',
                'code'    => 'already_submitted',
            ], 409);
        }

        // Validate details are filled
        $missing = [];
        foreach (['full_name', 'phone_number', 'vehicle_type', 'working_city'] as $field) {
            if (empty($boy->$field)) $missing[] = $field;
        }
        if (!empty($missing)) {
            return response()->json([
                'status'  => false,
                'code'    => 'incomplete_details',
                'message' => 'Please complete your details first.',
                'missing' => $missing,
            ], 422);
        }

        // Validate profile photo
        if (!$boy->picture) {
            return response()->json([
                'status'  => false,
                'code'    => 'no_photo',
                'message' => 'Please upload your profile photo.',
            ], 422);
        }

        // Validate all 7 documents uploaded
        $uploadedTypes = $boy->documents()->pluck('doc_type')->toArray();
        $missingDocs = array_diff($this->requiredDocs, $uploadedTypes);
        if (!empty($missingDocs)) {
            return response()->json([
                'status'  => false,
                'code'    => 'incomplete_documents',
                'message' => 'Please upload all required documents.',
                'missing' => array_values($missingDocs),
            ], 422);
        }

        $boy->update([
            'status'                => 'pending',
            'onboarding_step'       => 5,
            'has_pending_submission'=> true,
        ]);

        try {
            Mail::to($boy->email)->send(new DeliveryDocumentsSubmittedMail(
                $boy->full_name,
                $boy->email
            ));
        } catch (\Exception $e) {
            Log::error('[DeliveryOnboarding] Submission mail failed: ' . $e->getMessage());
        }

        Log::info('[DeliveryOnboarding] Boy submitted for review', [
            'delivery_boy_id' => $boy->id,
            'email'           => $boy->email,
        ]);

        return response()->json([
            'status'          => true,
            'message'         => 'Application submitted! We will verify your documents within 24 hours.',
            'onboarding_step' => 5,
            'boy_status'      => 'pending',
        ]);
    }

    // =========================================================================
    // GET — Onboarding Status
    // GET /delivery/onboarding/status
    // =========================================================================
    public function getStatus(Request $request): JsonResponse
    {
        $boy   = $request->user();
        $docs  = $boy->documents()->get();
        $uploadedTypes = $docs->pluck('doc_type')->toArray();
        $missingDocs   = array_diff($this->requiredDocs, $uploadedTypes);

        $checklist = [
            'email_verified'  => !empty($boy->otp) || $boy->onboarding_step >= 1,
            'details_filled'  => !empty($boy->full_name) && !empty($boy->phone_number) && !empty($boy->working_city),
            'photo_uploaded'  => !empty($boy->picture),
            'documents_uploaded' => empty($missingDocs),
            'submitted'       => in_array($boy->status, ['pending', 'active']),
        ];

        // Build timeline with timestamps
        $timeline = $this->buildTimeline($boy, $docs, $checklist, $uploadedTypes);

        return response()->json([
            'status'          => true,
            'boy_status'      => $boy->status,
            'is_verified'     => $boy->is_verified,
            'onboarding_step' => $boy->onboarding_step,
            'checklist'       => $checklist,
            'next_step'       => $this->nextStep($checklist, $boy),
            'timeline'        => $timeline,
            'data'            => $this->boySummary($boy),
            'documents'       => $docs->map(fn($d) => [
                'doc_type'    => $d->doc_type,
                'file_url'    => asset('storage/' . $d->file_path),
                'status'      => $d->status,
                'remarks'     => $d->remarks,
                'uploaded_at' => $d->uploaded_at?->toISOString(),
            ]),
            'progress' => [
                'uploaded'     => count(array_intersect($this->requiredDocs, $uploadedTypes)),
                'total'        => count($this->requiredDocs),
                'missing'      => array_values($missingDocs),
                'percentage'   => round((count(array_intersect($this->requiredDocs, $uploadedTypes)) / count($this->requiredDocs)) * 100),
            ],
        ]);
    }

    private function buildTimeline(DeliveryBoy $boy, $docs, array $checklist, array $uploadedTypes): array
    {
        $timeline = [];
        $now = now();

        // Step 1: Account Created
        $timeline[] = [
            'step'       => 1,
            'title'      => 'Account Created',
            'subtitle'   => 'Email verified successfully',
            'icon'       => 'email',
            'completed'  => $checklist['email_verified'],
            'timestamp'  => $boy->created_at?->toISOString(),
            'status'     => $checklist['email_verified'] ? 'completed' : 'pending',
        ];

        // Step 2: Personal Details
        $submittedAt = $checklist['details_filled'] ? $boy->updated_at?->toISOString() : null;
        $timeline[] = [
            'step'       => 2,
            'title'      => 'Personal Details',
            'subtitle'   => $checklist['details_filled']
                ? 'Name, phone, vehicle & city added'
                : 'Complete your profile information',
            'icon'       => 'person',
            'completed'  => $checklist['details_filled'],
            'timestamp'  => $submittedAt,
            'status'     => $checklist['details_filled'] ? 'completed' : ($boy->onboarding_step >= 1 ? 'in_progress' : 'pending'),
        ];

        // Step 3: Profile Photo
        $timeline[] = [
            'step'       => 3,
            'title'      => 'Profile Photo',
            'subtitle'   => $checklist['photo_uploaded']
                ? 'Photo uploaded for verification'
                : 'Upload a clear photo of yourself',
            'icon'       => 'camera_alt',
            'completed'  => $checklist['photo_uploaded'],
            'timestamp'  => $checklist['photo_uploaded'] ? $boy->updated_at?->toISOString() : null,
            'status'     => $checklist['photo_uploaded'] ? 'completed' : ($boy->onboarding_step >= 2 ? 'in_progress' : 'pending'),
        ];

        // Step 4: Documents
        $docCount = count(array_intersect($this->requiredDocs, $uploadedTypes));
        $totalDocs = count($this->requiredDocs);
        $lastDocUpload = $docs->sortByDesc('uploaded_at')->first()?->uploaded_at;
        $timeline[] = [
            'step'       => 4,
            'title'      => 'Documents',
            'subtitle'   => $checklist['documents_uploaded']
                ? 'All 7 documents uploaded'
                : "$docCount of $totalDocs documents uploaded",
            'icon'       => 'description',
            'completed'  => $checklist['documents_uploaded'],
            'timestamp'  => $checklist['documents_uploaded'] ? $lastDocUpload?->toISOString() : null,
            'status'     => $checklist['documents_uploaded'] ? 'completed' : ($docCount > 0 ? 'in_progress' : 'pending'),
        ];

        // Step 5: Submitted for Review
        $submittedTime = in_array($boy->status, ['pending', 'active']) ? $boy->updated_at?->toISOString() : null;
        $timeline[] = [
            'step'       => 5,
            'title'      => 'Submitted for Review',
            'subtitle'   => $boy->status === 'pending'
                ? 'Application is under admin review'
                : ($boy->is_verified
                    ? 'Account verified!'
                    : 'Submit your application'),
            'icon'       => 'send',
            'completed'  => in_array($boy->status, ['pending', 'active']),
            'timestamp'  => $submittedTime,
            'status'     => $boy->is_verified ? 'completed' : ($boy->status === 'pending' ? 'in_progress' : 'pending'),
        ];

        // Step 6: Admin Verification (only if submitted)
        if ($boy->status === 'pending' || $boy->is_verified) {
            $timeline[] = [
                'step'       => 6,
                'title'      => 'Admin Verification',
                'subtitle'   => $boy->is_verified
                    ? 'Account fully verified!'
                    : 'Waiting for admin to verify documents',
                'icon'       => 'verified',
                'completed'  => $boy->is_verified,
                'timestamp'  => $boy->is_verified ? $boy->updated_at?->toISOString() : null,
                'status'     => $boy->is_verified ? 'completed' : 'in_progress',
            ];
        }

        return $timeline;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function boySummary(DeliveryBoy $boy): array
    {
        return [
            'id'               => $boy->id,
            'full_name'        => $boy->full_name,
            'email'            => $boy->email,
            'phone_number'     => $boy->phone_number,
            'vehicle_type'     => $boy->vehicle_type,
            'working_city'     => $boy->working_city,
            'working_city_lat' => $boy->working_city_lat,
            'working_city_lng' => $boy->working_city_lng,
            'picture_url'      => $boy->picture ? asset('storage/' . $boy->picture) : null,
            'status'           => $boy->status,
            'is_verified'      => $boy->is_verified,
            'onboarding_step'  => $boy->onboarding_step,
        ];
    }

    private function nextStep(array $checklist, DeliveryBoy $boy): string
    {
        if ($boy->status === 'blocked') return 'blocked';
        if ($boy->is_verified) return 'approved';
        if (!$checklist['details_filled']) return 'fill_details';
        if (!$checklist['photo_uploaded']) return 'upload_photo';
        if (!$checklist['documents_uploaded']) return 'upload_documents';
        if (!$checklist['submitted']) return 'submit_for_review';
        return 'awaiting_approval';
    }
}
