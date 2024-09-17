<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthService
{
    /**
     * Authenticate a user and return a token.
     *
     * @param array $validatedData
     * @return JsonResponse
     */
    public function login(array $validatedData): JsonResponse
    {
        $token = Auth::attempt($validatedData);

        if ($token) {
            return $this->responseWithToken($token, Auth::user());
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'Invalid credentials',
        ], 403);
    }

    /**
     * Refresh the authentication token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            $newToken = Auth::refresh();
            return $this->responseWithToken($newToken, Auth::user());
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Token has expired',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Could not refresh the token',
            ], 500);
        }
    }

    /**
     * Log the user out.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'User has been logged out',
        ]);
    }

    /**
     * Generate a response with the authentication token and user details.
     *
     * @param $token
     * @param $user
     * @return JsonResponse
     */
    protected function responseWithToken($token, $user): JsonResponse
    {
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60,
            'token_type' => 'bearer',
        ]);
    }
}
