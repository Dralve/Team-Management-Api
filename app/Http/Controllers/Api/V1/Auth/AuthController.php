<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;

        $this->middleware('auth:api', [
            'except' => ['login']
        ]);
    }

    /**
     * Handle user login and return a JWT token.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->authService->login($request->validated());
    }

    /**
     * Refresh the JWT token for the authenticated user.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->authService->refresh();
    }

    /**
     * Logout the authenticated user and invalidate the JWT token.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        return $this->authService->logout();
    }

    /**
     * Get the current authenticated user.
     *
     * @return JsonResponse
     */
    public function current(): JsonResponse
    {
        return response()->json(auth()->user());
    }
}
