<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Provider\ProviderForgotPasswordRequest;
use App\Http\Requests\Api\Provider\ProviderLoginRequest;
use App\Http\Requests\Api\Provider\ProviderPhoneLoginRequest;
use App\Http\Requests\Api\Provider\ProviderRegisterRequest;
use App\Http\Requests\Api\Provider\ProviderResetPasswordRequest;
use App\Http\Requests\Api\Provider\ProviderSocialLoginRequest;
use App\Http\Resources\Provider\ProviderResource;
use App\Models\AppUser;
use App\Services\Provider\ProviderAuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderAuthController extends Controller
{
    public function __construct(protected ProviderAuthService $authService)
    {
    }

    public function register(ProviderRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        if (! empty($result['pending_approval']) || empty($result['token'])) {
            return ApiResponse::success([
                'pending_approval' => true,
                'provider' => new ProviderResource($result['user']),
            ], 'Registration successful. Your account is pending admin approval.');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new ProviderResource($result['user']),
            'Registration successful',
            'provider'
        );
    }

    public function login(ProviderLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('fcmToken')
        );

        return ApiResponse::authSuccess(
            $result['token'],
            new ProviderResource($result['user']),
            'Login successful',
            'provider'
        );
    }

    public function loginWithApple(ProviderSocialLoginRequest $request): JsonResponse
    {
        return $this->socialLoginResponse(
            $this->authService->loginWithApple(
                $request->input('id_token'),
                $request->validated()
            )
        );
    }

    public function loginWithPhone(ProviderPhoneLoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithPhone($request->validated());

        if (! empty($result['is_new_user']) && empty($result['token'])) {
            return ApiResponse::success([
                'is_new_user' => true,
                'profile' => $result['profile'] ?? null,
            ], 'Registration required');
        }

        if (! empty($result['pending_approval']) || empty($result['token'])) {
            return ApiResponse::success([
                'pending_approval' => true,
                'provider' => isset($result['user']) ? new ProviderResource($result['user']) : null,
            ], 'Account pending approval');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new ProviderResource($result['user']),
            'Login successful',
            'provider'
        );
    }

    public function forgotPassword(ProviderForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('email'));

        return ApiResponse::success(
            null,
            'If a matching provider account exists, a password reset email has been sent.'
        );
    }

    public function resetPassword(ProviderResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            $request->input('email'),
            $request->input('token'),
            $request->input('password')
        );

        return ApiResponse::success(null, 'Password reset successful');
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $this->authService->logout($user);

        return ApiResponse::success(null, 'Logout successful');
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $this->authService->deleteAccount($user);

        return ApiResponse::success(null, 'Account deleted');
    }

    protected function socialLoginResponse(array $result): JsonResponse
    {
        if (! empty($result['is_new_user']) && empty($result['token'])) {
            return ApiResponse::success([
                'is_new_user' => true,
                'profile' => $result['profile'],
            ], 'Registration required');
        }

        if (! empty($result['pending_approval']) || empty($result['token'])) {
            return ApiResponse::success([
                'pending_approval' => true,
                'provider' => isset($result['user']) ? new ProviderResource($result['user']) : null,
            ], 'Account pending approval');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new ProviderResource($result['user']),
            'Login successful',
            'provider'
        );
    }
}
