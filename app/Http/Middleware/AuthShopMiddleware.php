<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AppOwnerUser;
use Symfony\Component\HttpFoundation\Response;

class AuthShopMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken()
            ?? $request->header('X-Shop-Token')
            ?? $request->input('shop_token');

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Shop authentication required.'], 401);
        }

        $shop = AppOwnerUser::where('api_token', $token)->first();

        if (!$shop) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired shop token.'], 401);
        }

        if ($shop->status === 'blocked') {
            return response()->json(['status' => false, 'message' => 'Your store has been suspended. Please contact support.'], 403);
        }

        // pending vendors can manage products while awaiting approval
        // draft vendors are still in onboarding — block product access
        if ($shop->status === 'draft') {
            return response()->json(['status' => false, 'message' => 'Please complete your onboarding first.'], 403);
        }

        $shop->updateQuietly(['last_active_at' => now()]);

        $request->setUserResolver(fn () => $shop);

        return $next($request);
    }
}
