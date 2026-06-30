<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\AppUserResource;
use App\Models\AppUser;
use App\Services\Auth\AppAuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AppAuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::success([
            'user' => new AppUserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Registered', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('fcmToken')
        );

        return ApiResponse::success([
            'user' => new AppUserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $this->authService->logout($user);

        return ApiResponse::success(null, 'Logged out');
    }

    public function refresh(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success([
            'token' => $this->authService->refresh($user),
            'token_type' => 'Bearer',
        ], 'Token refreshed');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new AppUserResource($request->user()));
    }
}
