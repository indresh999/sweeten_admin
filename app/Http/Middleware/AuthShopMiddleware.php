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

        if ($shop->status === 'pending') {
            return response()->json(['status' => false, 'message' => 'Your store is pending approval. You will be notified once activated.'], 403);
        }

        $shop->updateQuietly(['last_active_at' => now()]);

        $request->setUserResolver(fn () => $shop);

        return $next($request);
    }
}
