<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessShopPhoto;
use App\Mail\SendOtpMail;
use App\Models\AppOwnerUser;
use App\Models\ShopDocument;
use App\Models\ShopImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class VendorOnboardingController extends Controller
{
    // =========================================================================
    // STEP 0A — Send Signup OTP
    // POST /shop/signup/send-otp
    // Public, rate-limited (5/min)
    // =========================================================================
    public function sendSignupOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $email = strtolower(trim($request->email));
        $shop  = AppOwnerUser::where('email', $email)->first();

        // Email registered and already past draft stage → redirect to login
        if ($shop && $shop->status !== 'draft') {
            return response()->json([
                'status'  => false,
                'code'    => 'already_registered',
                'message' => 'This email is already registered. Please use the login flow.',
            ], 409);
        }

        $otp = random_int(1000, 9999);

        try {
            DB::transaction(function () use ($email, $otp, $shop) {
                if ($shop) {
                    // Draft account exists → refresh OTP
                    $shop->update([
                        'otp_code'       => (string) $otp,
                        'otp_expires_at' => now()->addMinutes(10),
                    ]);
                } else {
                    // First touch → create a minimal draft record
                    AppOwnerUser::create([
                        'email'          => $email,
                        'status'         => 'draft',
                        'otp_code'       => (string) $otp,
                        'otp_expires_at' => now()->addMinutes(10),
                    ]);
                }

                Mail::to($email)->send(new SendOtpMail($otp, $email));
            });
        } catch (\Exception $e) {
            Log::error('[Onboarding] OTP send failed for ' . $email . ': ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to send OTP. Please try again.'], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'OTP sent to ' . $email . '. Valid for 10 minutes.',
        ]);
    }

    // =========================================================================
    // STEP 0B — Verify Signup OTP
    // POST /shop/signup/verify-otp
    // Public, rate-limited (5/min)
    // Returns: onboarding_token + current step so the app knows where to resume
    // =========================================================================
    public function verifySignupOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $email = strtolower(trim($request->email));
        $shop  = AppOwnerUser::where('email', $email)->first();

        if (!$shop || $shop->otp_code !== (string) $request->otp) {
            return response()->json(['status' => false, 'message' => 'Invalid OTP. Please try again.'], 400);
        }

        if (!$shop->otp_expires_at || now()->gt($shop->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP has expired. Please request a new one.'], 400);
        }

        // Consume OTP immediately → replay-proof
        $shop->update(['otp_code' => null, 'otp_expires_at' => null]);

        // Generate onboarding token
        $token = $shop->generateToken();

        return response()->json([
            'status'          => true,
            'message'         => 'Email verified. Continue to fill your shop details.',
            'token'           => $token,
            'onboarding_step' => $shop->onboarding_step,
            'shop'            => $this->shopSummary($shop),
        ]);
    }

    // =========================================================================
    // STEP 1 — Fill Shop Details
    // PUT /shop/onboarding/details
    // Protected: auth.onboarding (draft or pending)
    // Required fields validated here for the first time
    // =========================================================================
    public function updateDetails(Request $request): JsonResponse
    {
        $shop = $request->user();

        if (!in_array($shop->status, ['draft', 'pending'])) {
            return response()->json(['status' => false, 'message' => 'Details can only be updated during onboarding.'], 403);
        }

        $validator = Validator::make($request->all(), [
            // Owner identity
            'full_name'           => 'required|string|max:100',
            'phone_number'        => 'required|string|regex:/^[6-9]\d{9}$/|unique:app_owner_shops,phone_number,' . $shop->shop_id . ',shop_id',

            // Shop identity
            'restaurant_name'     => 'required|string|max:150',
            'restaurant_address'  => 'required|string|max:500',
            'city'                => 'required|string|max:100',
            'state'               => 'required|string|max:100',
            'zip_code'            => 'required|string|regex:/^\d{6}$/',
            'country'             => 'nullable|string|max:100',

            // Location
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',

            // Legal (optional at signup, required before approval by admin)
            'gst_number'          => 'nullable|string|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'pan_number'          => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'fssai_number'        => 'required|string|max:20',

            // Payment (optional at signup)
            'payment_type'        => 'nullable|in:bank,upi',
            'bank_account_name'   => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_ifsc'           => 'nullable|string|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_name'           => 'nullable|string|max:100',
            'upi_id'              => 'nullable|string|max:100',
        ], [
            'phone_number.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'zip_code.regex'     => 'Enter a valid 6-digit PIN code.',
            'gst_number.regex'   => 'Enter a valid GST number (e.g. 22AAAAA0000A1Z5).',
            'pan_number.regex'   => 'Enter a valid PAN number (e.g. ABCDE1234F).',
            'bank_ifsc.regex'    => 'Enter a valid IFSC code (e.g. SBIN0001234).',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $shop->update(array_merge(
            $validator->validated(),
            [
                'country'         => $request->input('country', 'India'),
                'onboarding_step' => max($shop->onboarding_step, 1),
            ]
        ));

        return response()->json([
            'status'          => true,
            'message'         => 'Shop details saved.',
            'onboarding_step' => $shop->fresh()->onboarding_step,
            'shop'            => $this->shopSummary($shop->fresh()),
        ]);
    }

    // =========================================================================
    // STEP 1B — Update Payment / Bank Details (standalone)
    // PUT /shop/onboarding/payment
    // Protected: auth.onboarding
    // =========================================================================
    public function updatePayment(Request $request): JsonResponse
    {
        $shop = $request->user();

        $validator = Validator::make($request->all(), [
            'payment_type'        => 'required|in:bank,upi',
            // Bank fields
            'bank_account_name'   => 'required_if:payment_type,bank|nullable|string|max:100',
            'bank_account_number' => 'required_if:payment_type,bank|nullable|string|max:30',
            'bank_ifsc'           => 'required_if:payment_type,bank|nullable|string|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_name'           => 'required_if:payment_type,bank|nullable|string|max:100',
            // UPI fields
            'upi_id'              => 'required_if:payment_type,upi|nullable|string|max:100',
        ], [
            'bank_ifsc.regex'     => 'Enter a valid IFSC code (e.g. SBIN0001234)',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($data['payment_type'] === 'upi') {
            $data['bank_account_name'] = null;
            $data['bank_account_number'] = null;
            $data['bank_ifsc'] = null;
            $data['bank_name'] = null;
        } else {
            $data['upi_id'] = null;
        }

        $shop->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Payment details saved.',
            'shop'    => $this->shopSummary($shop->fresh()),
        ]);
    }

    // =========================================================================
    // STEP 2 — Upload Shop Photos (job-based)
    // POST /shop/onboarding/photos
    // Protected: auth.onboarding
    // Accepts: photos[] files, tag (logo|cover|gallery)
    // Each file dispatches ProcessShopPhoto job → resized in background
    // =========================================================================
    public function uploadPhotos(Request $request): JsonResponse
    {
        $shop = $request->user();

        $validator = Validator::make($request->all(), [
            'photos'   => 'required|array|min:1|max:8',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240', // 10 MB
            'tag'      => 'nullable|in:logo,cover,gallery,item',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $tag       = $request->input('tag', 'gallery');
        $nextOrder = ShopImage::where('shop_id', $shop->shop_id)->max('sort_order') ?? -1;
        $created   = [];

        foreach ($request->file('photos') as $file) {
            $rawPath = $file->store('shop_raw/' . $shop->shop_id, 'public');

            $photo = ShopImage::create([
                'shop_id'           => $shop->shop_id,
                'tag'               => $tag,
                'image_path'        => $rawPath,   // raw until job finishes
                'original_path'     => $rawPath,
                'sort_order'        => ++$nextOrder,
                'processing_status' => 'pending',
            ]);

            ProcessShopPhoto::dispatchSync($photo->id);

            $created[] = $this->formatPhoto($photo->fresh());
        }

        // Advance onboarding step to at least 2 once photos are queued
        if ($shop->onboarding_step < 2) {
            $shop->update(['onboarding_step' => 2]);
        }

        return response()->json([
            'status'  => true,
            'message' => count($created) . ' photo(s) uploaded. Processing in background.',
            'data'    => $created,
        ], 201);
    }

    // =========================================================================
    // STEP 2 — List Uploaded Photos
    // GET /shop/onboarding/photos
    // Protected: auth.onboarding
    // Poll this to check processing_status of each photo
    // =========================================================================
    public function listPhotos(Request $request): JsonResponse
    {
        $photos = ShopImage::where('shop_id', $request->user()->shop_id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($p) => $this->formatPhoto($p));

        return response()->json(['status' => true, 'data' => $photos]);
    }

    // =========================================================================
    // STEP 2 — Delete a Photo
    // DELETE /shop/onboarding/photos/{id}
    // Protected: auth.onboarding
    // =========================================================================
    public function deletePhoto(Request $request, int $id): JsonResponse
    {
        $photo = ShopImage::where('shop_id', $request->user()->shop_id)->find($id);

        if (!$photo) {
            return response()->json(['status' => false, 'message' => 'Photo not found.'], 404);
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        foreach ([$photo->original_path, $photo->image_path, $photo->thumb_path] as $path) {
            if ($path) $disk->delete($path);
        }

        $photo->delete();

        return response()->json(['status' => true, 'message' => 'Photo deleted.']);
    }

    // =========================================================================
    // STEP 2 — Reorder Photos
    // POST /shop/onboarding/photos/reorder
    // Body: { order: [{id: 1, sort_order: 0}, {id: 2, sort_order: 1}] }
    // =========================================================================
    public function reorderPhotos(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order'              => 'required|array|min:1',
            'order.*.id'         => 'required|integer',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $shopId = $request->user()->shop_id;

        foreach ($request->order as $entry) {
            ShopImage::where('shop_id', $shopId)
                ->where('id', $entry['id'])
                ->update(['sort_order' => $entry['sort_order']]);
        }

        return response()->json(['status' => true, 'message' => 'Photo order updated.']);
    }

    // =========================================================================
    // STEP 3 — Submit for Review
    // POST /shop/onboarding/submit
    // Protected: auth.onboarding
    // Validates minimum requirements before allowing submission
    // =========================================================================
    public function submitForReview(Request $request): JsonResponse
    {
        $shop = $request->user();

        if ($shop->status === 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Your application is already submitted and under review.',
                'code'    => 'already_submitted',
            ], 409);
        }

        if (!in_array($shop->status, ['draft', 'rejected'])) {
            return response()->json(['status' => false, 'message' => 'Only draft or rejected shops can submit for review.'], 403);
        }

        // Validate minimum required details are filled
        $missing = [];
        foreach (['full_name', 'phone_number', 'restaurant_name', 'restaurant_address', 'city', 'state', 'zip_code', 'fssai_number'] as $field) {
            if (empty($shop->$field)) $missing[] = $field;
        }

        if (!empty($missing)) {
            return response()->json([
                'status'  => false,
                'code'    => 'incomplete_details',
                'message' => 'Please complete your shop details before submitting.',
                'missing' => $missing,
            ], 422);
        }

        // At least 1 photo required
        $photoCount = ShopImage::where('shop_id', $shop->shop_id)->count();
        if ($photoCount === 0) {
            return response()->json([
                'status'  => false,
                'code'    => 'no_photos',
                'message' => 'Please upload at least one shop photo before submitting.',
            ], 422);
        }

        $shop->update([
            'status'          => 'pending',
            'onboarding_step' => 3,
        ]);

        // Notify admin (log for now; wire to notification/email as needed)
        Log::info('[Onboarding] Shop submitted for review', [
            'shop_id'         => $shop->shop_id,
            'email'           => $shop->email,
            'restaurant_name' => $shop->restaurant_name,
        ]);

        return response()->json([
            'status'          => true,
            'message'         => 'Application submitted! Our team will review and activate your store within 24 hours.',
            'onboarding_step' => 3,
            'shop_status'     => 'pending',
        ]);
    }

    // =========================================================================
    // GET Current Onboarding Status
    // GET /shop/onboarding/status
    // Protected: auth.onboarding
    // Used by the app to resume a partial onboarding session
    // =========================================================================
    public function getStatus(Request $request): JsonResponse
    {
        $shop   = $request->user();
        $photos = ShopImage::where('shop_id', $shop->shop_id)->orderBy('sort_order')->get();

        $docs = ShopDocument::where('shop_id', $shop->shop_id)->get();

        $checklist = [
            'email_verified'      => true,  // reaching here means OTP was already verified
            'details_filled'      => $this->detailsFilled($shop),
            'photos_uploaded'     => $photos->count() > 0,
            'photos_ready'        => $photos->where('processing_status', 'done')->count() > 0,
            'documents_uploaded'  => $docs->count() > 0,
            'submitted'           => $shop->status === 'pending',
        ];

        return response()->json([
            'status'          => true,
            'shop_status'     => $shop->status,
            'onboarding_step' => $shop->onboarding_step,
            'checklist'       => $checklist,
            'next_step'       => $this->nextStep($checklist, $shop->status),
            'shop'            => $this->shopSummary($shop),
            'photos'          => $photos->map(fn($p) => $this->formatPhoto($p)),
            'documents'       => $docs->map(fn($d) => $this->formatDocument($d)),
        ]);
    }

    // =========================================================================
    // STEP 3 — Upload Shop Documents
    // POST /shop/onboarding/documents
    // Body (multipart): file (image/pdf, max 10 MB), doc_type, doc_label (for 'other')
    // =========================================================================
    public function uploadDocument(Request $request): JsonResponse
    {
        $shop = $request->user();

        $validator = Validator::make($request->all(), [
            'file'      => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'doc_type'  => 'required|string|in:' . implode(',', array_keys(ShopDocument::$knownTypes)),
            'doc_label' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $file     = $request->file('file');
        $docType  = $request->input('doc_type');
        $dir      = 'shop_documents/' . $shop->shop_id;
        $path     = $file->store($dir, 'public');

        $doc = ShopDocument::create([
            'shop_id'       => $shop->shop_id,
            'doc_type'      => $docType,
            'doc_label'     => $request->input('doc_label'),
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size'     => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
            'status'        => 'pending',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Document uploaded successfully.',
            'data'    => $this->formatDocument($doc),
        ], 201);
    }

    // =========================================================================
    // STEP 3 — List Uploaded Documents
    // GET /shop/onboarding/documents
    // =========================================================================
    public function listDocuments(Request $request): JsonResponse
    {
        $docs = ShopDocument::where('shop_id', $request->user()->shop_id)
            ->orderBy('created_at')
            ->get()
            ->map(fn($d) => $this->formatDocument($d));

        return response()->json(['status' => true, 'data' => $docs]);
    }

    // =========================================================================
    // STEP 3 — Delete a Document
    // DELETE /shop/onboarding/documents/{id}
    // =========================================================================
    public function deleteDocument(Request $request, int $id): JsonResponse
    {
        $doc = ShopDocument::where('shop_id', $request->user()->shop_id)->find($id);

        if (!$doc) {
            return response()->json(['status' => false, 'message' => 'Document not found.'], 404);
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['status' => true, 'message' => 'Document deleted.']);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function shopSummary(AppOwnerUser $shop): array
    {
        return [
            'shop_id'          => $shop->shop_id,
            'email'            => $shop->email,
            'full_name'        => $shop->full_name,
            'phone_number'     => $shop->phone_number,
            'restaurant_name'  => $shop->restaurant_name,
            'restaurant_address'=> $shop->restaurant_address,
            'city'             => $shop->city,
            'state'            => $shop->state,
            'zip_code'         => $shop->zip_code,
            'country'          => $shop->country,
            'latitude'         => $shop->latitude,
            'longitude'        => $shop->longitude,
            'gst_number'       => $shop->gst_number,
            'pan_number'       => $shop->pan_number,
            'fssai_number'     => $shop->fssai_number,
            'payment_type'     => $shop->payment_type,
            'bank_account_name'   => $shop->bank_account_name,
            'bank_account_number' => $shop->bank_account_number
                ? substr($shop->bank_account_number, -4)
                : null,
            'bank_ifsc'           => $shop->bank_ifsc,
            'bank_name'           => $shop->bank_name,
            'upi_id'              => $shop->upi_id,
            'status'           => $shop->status,
            'onboarding_step'  => $shop->onboarding_step,
            'created_at'       => $shop->created_at,
        ];
    }

    private function formatDocument(ShopDocument $d): array
    {
        return [
            'id'            => $d->id,
            'doc_type'      => $d->doc_type,
            'doc_label'     => $d->label,
            'url'           => $d->url,
            'original_name' => $d->original_name,
            'mime_type'     => $d->mime_type,
            'file_size'     => $d->file_size,
            'status'        => $d->status,
            'remarks'       => $d->remarks,
            'created_at'    => $d->created_at?->toISOString(),
        ];
    }

    private function formatPhoto(ShopImage $p): array
    {
        $processedUrl = $p->image_path ? asset('storage/' . $p->image_path) : null;
        $thumbUrl     = $p->thumb_path ? asset('storage/' . $p->thumb_path) : $processedUrl;
        $rawUrl       = $p->original_path ? asset('storage/' . $p->original_path) : null;

        return [
            'id'                => $p->id,
            'tag'               => $p->tag,
            'url'               => $processedUrl ?? $rawUrl,
            'thumb_url'         => $thumbUrl ?? $rawUrl,
            'sort_order'        => $p->sort_order,
            'processing_status' => $p->processing_status,
            'processing_error'  => $p->processing_error,
        ];
    }

    private function detailsFilled(AppOwnerUser $shop): bool
    {
        foreach (['full_name', 'phone_number', 'restaurant_name', 'restaurant_address', 'city', 'state', 'zip_code', 'fssai_number'] as $field) {
            if (empty($shop->$field)) return false;
        }
        return true;
    }

    private function nextStep(array $checklist, string $shopStatus): string
    {
        if ($shopStatus === 'active')   return 'approved';
        if ($shopStatus === 'pending')  return 'awaiting_approval';
        if ($shopStatus === 'rejected') return 'submit_for_review';
        if (!$checklist['details_filled'])     return 'fill_details';
        if (!$checklist['photos_uploaded'])    return 'upload_photos';
        if (!$checklist['documents_uploaded']) return 'upload_documents';
        if (!$checklist['submitted'])          return 'submit_for_review';
        return 'awaiting_approval';
    }
}
