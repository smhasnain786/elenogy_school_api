<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CsrfToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class JwtCsrfMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Get token from Authorization header
            $authHeader = $request->header('Authorization');

            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return response()->json([
                    'error' => 'Authorization token not found'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $token = $matches[1];

            // Manually set the token for JWTAuth
            JWTAuth::setToken($token);
$user_id=null;
            // Try to authenticate user
            if (!$user = JWTAuth::authenticate()) {
              $user_id=$user->user_id;
                return response()->json([
                    'error' => 'User not found'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Add user to request
            $request->merge(['user' => $user]);
              $user_id=$user->user_id;

            // CSRF protection for state-changing requests
            if (in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
                $CsrfToken = $request->header('x-xsrf-token');

                if (!$CsrfToken) {
                    return response()->json([
                        'error' => 'CSRF token missing'
                    ], Response::HTTP_FORBIDDEN);
                }

                // Log for debugging
                Log::debug('CSRF Check', [
                    'user_id' => $user_id,
                    'received_token' => substr($CsrfToken, 0, 10) . '...',
                    'method' => $request->method()
                ]);

                // Fetch the stored token from database
                $storedToken = CsrfToken::where('user_id', $user_id)
                    ->where('expires_at', '>', now())
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$storedToken) {
                    return response()->json([
                        'error' => 'No valid CSRF token found for user',
                        'user_id' => $user_id
                    ], Response::HTTP_FORBIDDEN);
                }

                // Verify the token
                if (!Hash::check($CsrfToken, $storedToken->token)) {
                    return response()->json([
                        'error' => 'CSRF token mismatch'
                    ], Response::HTTP_FORBIDDEN);
                }
            }

            return $next($request);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'error' => 'Token has expired'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'error' => 'Token is invalid'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            // Catch-all for debugging other errors
            return response()->json([
                'error' => 'Authentication error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}