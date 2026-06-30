<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\AppUser;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    public function getBalance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $user   = AppUser::findOrFail($request->user_id);
        $wallet = $user->getOrCreateWallet();
        return response()->json(['status' => true, 'data' => ['balance' => $wallet->balance]]);
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'  => 'required|exists:app_users,id',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $txns = WalletTransaction::where('user_id', $request->user_id)
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json(['status' => true, 'data' => $txns]);
    }
}
