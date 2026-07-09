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

        try {
            Mail::to($email)->send(new SendOtpMail($otp, $email));
        } catch (\Exception $e) {
            Log::error('[DeliveryAuth] OTP mail failed for ' . $email . ': ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to send OTP. Please try again.'], 500);
        }

        return response()->json(['status' => true, 'message' => 'OTP sent to your email']);
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

        $isNewUser = !$boy->is_verified;

        $token = $boy->createToken('delivery-app')->plainTextToken;

        $boy->update([
            'otp'            => null,
            'otp_expires_at' => null,
            'is_verified'    => true,
            'last_login_at'  => now(),
            'status'         => 'online',
        ]);

        return response()->json([
            'status'      => true,
            'token'       => $token,
            'is_new_user' => $isNewUser,
            'data'        => $boy->makeHidden(['password']),
        ]);
    }

    // ── Register (optional — can be skipped, OTP auto-creates) ───────
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name'    => 'required|string|max:100',
            'phone_number' => 'required|string|max:20|unique:delivery_boys,phone_number',
            'password'     => 'required|string|min:6',
            'vehicle_type' => 'nullable|in:bike,bicycle,scooter,car',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);
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
        return response()->json(['status' => true, 'data' => $boy->makeHidden(['password'])]);
    }

    // ── Update Profile ────────────────────────────────────────────────
    public function update(Request $request): JsonResponse
    {
        $boy = $request->user();
        $validator = Validator::make($request->all(), [
            'full_name'           => 'sometimes|string|max:100',
            'phone_number'        => 'nullable|string|max:20',
            'vehicle_type'        => 'nullable|in:bike,scooter,cycle,car,bicycle,walking',
            'picture'             => 'nullable|string',
            'fcm_token'           => 'nullable|string',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_ifsc'           => 'nullable|string|max:20',
            'bank_account_name'   => 'nullable|string|max:100',
            'upi_id'              => 'nullable|string|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boy->update($validator->validated());
        return response()->json(['status' => true, 'data' => $boy->makeHidden(['password'])]);
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
        $boy->status = $boy->status === 'online' ? 'offline' : 'online';
        $boy->save();
        return response()->json(['status' => true, 'availability' => $boy->status]);
    }

    // ── Upload Document ───────────────────────────────────────────────
    public function uploadDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'doc_type' => 'required|in:aadhar,pan,driving_license,vehicle_rc,bank_passbook,selfie',
            'file'     => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boy  = $request->user();
        $path = $request->file('file')->store("delivery_documents/{$boy->id}", 'public');

        $doc = DeliveryDocument::updateOrCreate(
            ['delivery_boy_id' => $boy->id, 'doc_type' => $request->doc_type],
            ['file_path' => $path, 'status' => 'pending']
        );

        return response()->json([
            'status'  => true,
            'message' => 'Document uploaded. Awaiting approval.',
            'data'    => $doc,
        ], 201);
    }

    // ── List Documents ────────────────────────────────────────────────
    public function listDocuments(Request $request): JsonResponse
    {
        $docs = $request->user()->documents()->get();
        return response()->json(['status' => true, 'data' => $docs]);
    }
}
