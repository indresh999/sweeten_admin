<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Coupon;
use App\Models\CouponUsage;

class CouponController extends Controller
{
    public function validateCoupon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:app_users,id',
            'coupon_code'  => 'required|string',
            'order_amount' => 'required|numeric|min:0',
            'shop_id'      => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
        if (!$coupon) {
            return response()->json(['status' => false, 'message' => 'Coupon code not found'], 404);
        }
        if (!$coupon->isValid()) {
            return response()->json(['status' => false, 'message' => 'This coupon has expired or is no longer active'], 422);
        }
        if ((float)$request->order_amount < (float)$coupon->min_order_amount) {
            return response()->json(['status' => false, 'message' => "Add ₹" . ((float)$coupon->min_order_amount - (float)$request->order_amount) . " more to use this coupon"], 422);
        }
        $used = CouponUsage::where('coupon_id', $coupon->id)->where('user_id', $request->user_id)->count();
        if ($used >= $coupon->usage_per_user) {
            return response()->json(['status' => false, 'message' => 'You have already used this coupon the maximum number of times'], 422);
        }

        $discount = $coupon->calculateDiscount((float)$request->order_amount);

        return response()->json([
            'status'   => true,
            'message'  => 'Coupon is valid!',
            'coupon'   => $coupon->only(['id','code','title','description','discount_type','discount_value','max_discount_amount']),
            'discount' => $discount,
        ]);
    }

    public function adminIndex(): JsonResponse
    {
        $coupons = Coupon::orderByDesc('id')->paginate(20);
        return response()->json(['status' => true, 'data' => $coupons]);
    }

    public function adminCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code'               => 'required|string|max:50|unique:coupons,code',
            'title'              => 'required|string|max:150',
            'description'        => 'nullable|string',
            'discount_type'      => 'required|in:percent,flat',
            'discount_value'     => 'required|numeric|min:0',
            'min_order_amount'   => 'nullable|numeric|min:0',
            'max_discount_amount'=> 'nullable|numeric|min:0',
            'usage_limit'        => 'nullable|integer|min:1',
            'usage_per_user'     => 'nullable|integer|min:1',
            'applicable_to'      => 'nullable|in:all,category,shop,item',
            'applicable_ids'     => 'nullable|array',
            'valid_from'         => 'required|date',
            'valid_until'        => 'required|date|after:valid_from',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $data['code'] = strtoupper($data['code']);
        $coupon = Coupon::create($data);
        return response()->json(['status' => true, 'data' => $coupon], 201);
    }

    public function adminUpdate(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title'              => 'sometimes|string|max:150',
            'description'        => 'nullable|string',
            'discount_value'     => 'sometimes|numeric|min:0',
            'min_order_amount'   => 'nullable|numeric|min:0',
            'max_discount_amount'=> 'nullable|numeric|min:0',
            'usage_limit'        => 'nullable|integer|min:1',
            'valid_from'         => 'sometimes|date',
            'valid_until'        => 'sometimes|date',
            'is_active'          => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $coupon->update($validator->validated());
        return response()->json(['status' => true, 'data' => $coupon]);
    }

    public function adminDelete(int $id): JsonResponse
    {
        Coupon::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Coupon deleted']);
    }
}
