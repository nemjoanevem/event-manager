<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $userResource = $this->authService->register($request->validated());

        return response()->json([
            'message' => __('auth.register_success'),
            'user' => $userResource,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $userResource = $this->authService->login($request->validated());

        return response()->json([
            'message' => __('auth.login_success'),
            'user' => $userResource,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => __('auth.logout_success'),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $userResource = $this->authService->me($request->user());

        return response()->json([
            'user' => $userResource,
        ]);
    }
}
