<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token   = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token);
            $jti     = $payload->get('jti');
            $user    = JWTAuth::authenticate($token);

            if ($user && $user->current_jti && $user->current_jti !== $jti) {
                return response()->json(["status" => false, "message" => "Session invalidated", "errors" => null, "data" => null], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            // if token invalid or missing, let other middleware handle
        }

        return $next($request);
    }
}
