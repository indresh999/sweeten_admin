<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\DeliveryBoy;
use App\Models\DeliveryDocument;

class DeliveryBoyAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name'    => 'required|string|max:100',
            'phone_number' => 'required|string|max:20|unique:delivery_boys,phone_number',
            'password'     => 'required|string|min:6',
            'vehicle_type' => 'nullable|in:bike,bicycle,scooter,car',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        $boy = DeliveryBoy::create($data);
        $token = $boy->createToken('delivery-app')->plainTextToken;

        return response()->json(['status' => true, 'message' => 'Registered successfully. Awaiting verification.', 'data' => $boy->makeHidden(['password']), 'token' => $token], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'password'     => 'required|string',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

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

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['status' => 'offline']);
        $user->currentAccessToken()->delete();
        return response()->json(['status' => true, 'message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        $boy = $request->user()->load('documents');
        return response()->json(['status' => true, 'data' => $boy->makeHidden(['password'])]);
    }

    public function update(Request $request): JsonResponse
    {
        $boy = $request->user();
        $validator = Validator::make($request->all(), [
            'full_name'    => 'sometimes|string|max:100',
            'vehicle_type' => 'nullable|in:bike,bicycle,scooter,car',
            'password'     => 'nullable|string|min:6',
            'picture'      => 'nullable|string',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $data = $validator->validated();
        if (!empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);
        $boy->update($data);

        return response()->json(['status' => true, 'data' => $boy->makeHidden(['password'])]);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        $request->user()->update(['latitude' => $request->latitude, 'longitude' => $request->longitude]);
        return response()->json(['status' => true, 'message' => 'Location updated']);
    }

    public function toggleAvailability(Request $request): JsonResponse
    {
        $boy = $request->user();
        $boy->status = $boy->status === 'online' ? 'offline' : 'online';
        $boy->save();
        return response()->json(['status' => true, 'availability' => $boy->status]);
    }

    public function uploadDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'doc_type' => 'required|in:aadhar,pan,driving_license,vehicle_rc,bank_passbook,selfie',
            'file'     => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $boy  = $request->user();
        $path = $request->file('file')->store("delivery_documents/{$boy->id}", 'public');

        $doc = DeliveryDocument::updateOrCreate(
            ['delivery_boy_id' => $boy->id, 'doc_type' => $request->doc_type],
            ['file_path' => $path, 'status' => 'pending']
        );

        return response()->json(['status' => true, 'message' => 'Document uploaded. Awaiting approval.', 'data' => $doc], 201);
    }

    public function listDocuments(Request $request): JsonResponse
    {
        $docs = $request->user()->documents()->get();
        return response()->json(['status' => true, 'data' => $docs]);
    }
}
