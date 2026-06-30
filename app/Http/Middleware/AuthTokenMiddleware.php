<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AppUser;
use Symfony\Component\HttpFoundation\Response;

class AuthTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken()
            ?? $request->header('X-Api-Token')
            ?? $request->input('api_token');

        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $user = AppUser::where('api_token', $token)
            ->where('is_blocked', 0)
            ->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired token.'], 401);
        }

        // Attach user to request so controllers can use $request->authUser()
        $request->merge(['_auth_user' => $user]);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
