<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\DeliveryBoy;
use App\Models\DeliveryDocument;
use App\Mail\SendOtpMail;
use App\Mail\DeliveryDocumentsSubmittedMail;
use App\Mail\DeliveryAccountVerifiedMail;

class DeliveryBoyAuthController extends Controller
{
    // ── Send OTP (email-based) ────────────────────────────────────────
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $email = strtolower(trim($request->email));

        $boy = DeliveryBoy::where('email', $email)->first();

        // Block check
        if ($boy && $boy->status === 'blocked') {
            return response()->json(['status' => false, 'message' => 'Your account has been suspended. Contact support.'], 403);
        }

        $otp = random_int(1000, 9999);
        $isExistingUser = $boy !== null;

        if ($boy) {
            $boy->update([
                'otp'            => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);
        } else {
            // FirstOrCreate — new delivery boy
            $boy = DeliveryBoy::firstOrCreate(
                ['email' => $email],
                [
                    'full_name'     => Str::before($email, '@'),
                    'otp'           => $otp,
                    'otp_expires_at'=> Carbon::now()->addMinutes(10),
                ]
            );
        }

        // Send email asynchronously — don't block the response
        try {
            dispatch(fn() => Mail::to($email)->send(new SendOtpMail($otp, $email)));
        } catch (\Exception $e) {
            Log::error('[DeliveryAuth] OTP mail dispatch failed for ' . $email . ': ' . $e->getMessage());
        }

        return response()->json([
            'status'            => true,
            'message'           => 'OTP sent to your email',
            'is_existing_user'  => $isExistingUser,
        ]);
    }

    // ── Verify OTP ────────────────────────────────────────────────────
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|digits:4',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $email = strtolower(trim($request->email));
        $boy   = DeliveryBoy::where('email', $email)->where('otp', $request->otp)->first();

        if (!$boy) {
            return response()->json(['status' => false, 'message' => 'Invalid OTP. Please try again.'], 400);
        }
        if (!$boy->otp_expires_at || Carbon::now()->gt($boy->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP has expired. Please request a new one.'], 400);
        }

        $token = $boy->createToken('delivery-app')->plainTextToken;

        // Determine onboarding state
        $hasDetails = !empty($boy->phone_number) && $boy->phone_number !== '';
        $hasPhoto   = !empty($boy->picture);
        $uploadedDocs = $boy->documents()->count();
        $hasAllDocs   = $uploadedDocs >= 7;
        $allApproved  = $boy->documents()->where('status', 'approved')->count() >= 7;

        // New user = never filled profile details
        $isNewUser = !$hasDetails;

        $boy->update([
            'otp'            => null,
            'otp_expires_at' => null,
            'last_login_at'  => now(),
            'status'         => $allApproved ? 'online' : ($boy->status === 'blocked' ? 'blocked' : 'offline'),
        ]);

        // Load documents for response
        $boy->load('documents');

        return response()->json([
            'status'      => true,
            'token'       => $token,
            'is_new_user' => $isNewUser,
            'has_details' => $hasDetails,
            'has_photo'   => $hasPhoto,
            'has_all_docs'=> $hasAllDocs,
            'data'        => array_merge(
                $boy->makeHidden(['password'])->toArray(),
                ['documents_count' => $uploadedDocs]
            ),
        ]);
    }

    // ── Register (optional — can be skipped, OTP auto-creates) ───────
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name'    => 'required|string|max:100',
            'phone_number' => 'required|string|max:20|unique:delivery_boys,phone_number',
            'password'     => 'required|string|min:6',
            'vehicle_type' => 'nullable|in:bike,bicycle,scooter,car,walking',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $boy  = DeliveryBoy::create($data);
        $token = $boy->createToken('delivery-app')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Registered successfully. Awaiting verification.',
            'data'    => $boy->makeHidden(['password']),
            'token'   => $token,
        ], 201);
    }

    // ── Login (password-based — legacy) ───────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'password'     => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boy = DeliveryBoy::where('phone_number', $request->phone_number)->first();
        if (!$boy || !Hash::check($request->password, $boy->password)) {
            return response()->json(['status' => false, 'message' => 'Invalid credentials'], 401);
        }
        if ($boy->status === 'blocked') {
            return response()->json(['status' => false, 'message' => 'Your account has been suspended. Contact support.'], 403);
        }

        $boy->update(['last_login_at' => now(), 'status' => 'online']);
        $boy->tokens()->where('name', 'delivery-app')->delete();
        $token = $boy->createToken('delivery-app')->plainTextToken;

        return response()->json(['status' => true, 'data' => $boy->makeHidden(['password']), 'token' => $token]);
    }

    // ── Logout ────────────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['status' => 'offline']);
        $user->currentAccessToken()->delete();
        return response()->json(['status' => true, 'message' => 'Logged out']);
    }

    // ── Profile ───────────────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        $boy = $request->user()->load('documents');
        $data = $boy->makeHidden(['password'])->toArray();
        $data['picture_url'] = $boy->picture ? asset('storage/' . $boy->picture) : null;
        $data['documents_count'] = $boy->documents()->count();

        $data['bank_upi'] = [
            'type'                => $boy->payment_type,
            'bank_name'           => $boy->payment_type === 'bank' ? 'Bank Account' : null,
            'account_holder_name' => $boy->bank_account_name,
            'account_number'      => $boy->bank_account_number
                ? substr($boy->bank_account_number, -4)
                : null,
            'ifsc_code'           => $boy->bank_ifsc,
            'upi_id'              => $boy->upi_id,
        ];

        return response()->json(['status' => true, 'data' => $data]);
    }

    // ── Upload Profile Photo ──────────────────────────────────────────
    public function uploadPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boy = $request->user();

        // Delete old photo if exists
        if ($boy->picture && \Storage::disk('public')->exists($boy->picture)) {
            \Storage::disk('public')->delete($boy->picture);
        }

        $path = $request->file('photo')->store('delivery_boys/' . $boy->id, 'public');
        $boy->update(['picture' => $path]);

        return response()->json([
            'status'  => true,
            'message' => 'Profile photo updated.',
            'data'    => ['picture' => asset('storage/' . $path)],
        ]);
    }

    // ── Update Profile ────────────────────────────────────────────────
    public function update(Request $request): JsonResponse
    {
        $boy = $request->user();
        $validator = Validator::make($request->all(), [
            'full_name'           => 'sometimes|string|max:100',
            'phone_number'        => 'nullable|string|max:20',
            'vehicle_type'        => 'nullable|in:bike,scooter,cycle,car,bicycle,walking',
            'working_city'        => 'nullable|string|max:100',
            'working_city_lat'    => 'nullable|numeric|between:-90,90',
            'working_city_lng'    => 'nullable|numeric|between:-180,180',
            'fcm_token'           => 'nullable|string',
            'bank_upi'            => 'nullable|array',
            'bank_upi.payment_type' => 'nullable|in:bank,upi',
            'bank_upi.bank_name'  => 'nullable|string|max:100',
            'bank_upi.account_holder_name' => 'nullable|string|max:100',
            'bank_upi.account_number' => 'nullable|string|max:30',
            'bank_upi.ifsc_code'  => 'nullable|string|max:20',
            'bank_upi.upi_id'     => 'nullable|string|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Handle nested bank_upi payload
        if (isset($data['bank_upi']) && is_array($data['bank_upi'])) {
            $bankUpi = $data['bank_upi'];
            unset($data['bank_upi']);

            $data['payment_type'] = $bankUpi['payment_type'] ?? null;

            if (($bankUpi['payment_type'] ?? '') === 'bank') {
                $data['bank_account_number'] = $bankUpi['account_number'] ?? null;
                $data['bank_ifsc'] = $bankUpi['ifsc_code'] ?? null;
                $data['bank_account_name'] = $bankUpi['account_holder_name'] ?? null;
                $data['upi_id'] = null;
            } elseif (($bankUpi['payment_type'] ?? '') === 'upi') {
                $data['upi_id'] = $bankUpi['upi_id'] ?? null;
                $data['bank_account_number'] = null;
                $data['bank_ifsc'] = null;
                $data['bank_account_name'] = null;
            }
        }

        $boy->update($data);

        $boyData = $boy->makeHidden(['password'])->toArray();
        $boyData['bank_upi'] = [
            'type'               => $boy->payment_type,
            'bank_name'          => $boy->payment_type === 'bank' ? ($boy->bank_account_name ? 'Bank Account' : null) : null,
            'account_holder_name' => $boy->bank_account_name,
            'account_number'     => $boy->bank_account_number
                ? substr($boy->bank_account_number, -4)
                : null,
            'ifsc_code'          => $boy->bank_ifsc,
            'upi_id'             => $boy->upi_id,
        ];

        return response()->json(['status' => true, 'data' => $boyData]);
    }

    // ── Update Location ───────────────────────────────────────────────
    public function updateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $request->user()->update([
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json(['status' => true, 'message' => 'Location updated']);
    }

    // ── Toggle Availability ───────────────────────────────────────────
    public function toggleAvailability(Request $request): JsonResponse
    {
        $boy = $request->user();

        if ($boy->status === 'blocked') {
            return response()->json(['status' => false, 'message' => 'Your account is blocked. Contact support.'], 403);
        }

        if (!$boy->is_verified) {
            return response()->json(['status' => false, 'message' => 'Your account is not yet verified. Please wait for admin approval.'], 403);
        }

        $boy->status = $boy->status === 'online' ? 'offline' : 'online';
        $boy->save();
        return response()->json(['status' => true, 'availability' => $boy->status]);
    }

    // ── Upload Document ───────────────────────────────────────────────
    public function uploadDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'doc_type' => 'required|in:aadhar,pan,driving_license,vehicle_rc_front,vehicle_rc_back,bank_passbook,selfie',
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

        if ($existing && $existing->file_path && \Storage::disk('public')->exists($existing->file_path)) {
            \Storage::disk('public')->delete($existing->file_path);
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

        // Check if all 7 required docs are uploaded
        $requiredTypes = ['aadhar', 'pan', 'driving_license', 'vehicle_rc_front', 'vehicle_rc_back', 'bank_passbook', 'selfie'];
        $uploadedTypes = $boy->documents()->pluck('doc_type')->toArray();
        $allUploaded = count(array_intersect($requiredTypes, $uploadedTypes)) >= 7;

        // Send mail only once when all docs first submitted
        if ($allUploaded && !$boy->has_pending_submission) {
            $boy->update(['has_pending_submission' => true]);
            try {
                Mail::to($boy->email)->send(new DeliveryDocumentsSubmittedMail(
                    $boy->full_name,
                    $boy->email
                ));
            } catch (\Exception $e) {
                Log::error('[DeliveryAuth] Docs submitted mail failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Document uploaded successfully.',
            'data'    => [
                'id'         => $doc->id,
                'doc_type'   => $doc->doc_type,
                'file_path'  => $doc->file_path,
                'file_url'   => asset('storage/' . $doc->file_path),
                'status'     => $doc->status,
            ],
        ], 201);
    }

    // ── List Documents ────────────────────────────────────────────────
    public function listDocuments(Request $request): JsonResponse
    {
        $docs = $request->user()->documents()->get()->map(fn($d) => [
            'id'         => $d->id,
            'doc_type'   => $d->doc_type,
            'file_path'  => $d->file_path,
            'file_url'   => asset('storage/' . $d->file_path),
            'status'     => $d->status,
            'remarks'    => $d->remarks,
            'uploaded_at'=> $d->uploaded_at,
        ]);

        $requiredTypes = ['aadhar', 'pan', 'driving_license', 'vehicle_rc_front', 'vehicle_rc_back', 'bank_passbook', 'selfie'];
        $uploadedTypes = $docs->pluck('doc_type')->toArray();
        $missingTypes  = array_diff($requiredTypes, $uploadedTypes);

        return response()->json([
            'status' => true,
            'data'   => $docs,
            'meta'   => [
                'required'      => $requiredTypes,
                'uploaded'      => $uploadedTypes,
                'missing'       => array_values($missingTypes),
                'all_uploaded'  => empty($missingTypes),
                'approved_count'=> $docs->where('status', 'approved')->count(),
            ],
        ]);
    }

    // ── Document Verification Status ──────────────────────────────────
    public function documentStatus(Request $request): JsonResponse
    {
        $boy = $request->user();
        $docs = $boy->documents()->get();

        $requiredTypes = ['aadhar', 'pan', 'driving_license', 'vehicle_rc_front', 'vehicle_rc_back', 'bank_passbook', 'selfie'];

        $statuses = [];
        foreach ($requiredTypes as $type) {
            $doc = $docs->firstWhere('doc_type', $type);
            $statuses[$type] = $doc ? $doc->status : 'not_uploaded';
        }

        $approvedCount = count(array_filter($statuses, fn($s) => $s === 'approved'));
        $rejectedCount = count(array_filter($statuses, fn($s) => $s === 'rejected'));
        $allApproved   = $approvedCount >= 7;

        return response()->json([
            'status' => true,
            'data'   => [
                'documents'      => $statuses,
                'approved_count' => $approvedCount,
                'rejected_count' => $rejectedCount,
                'total_required' => 7,
                'all_approved'   => $allApproved,
                'can_go_online'  => $allApproved,
            ],
        ]);
    }
}
