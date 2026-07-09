<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Vendor\VendorForgotPasswordRequest;
use App\Http\Requests\Api\Vendor\VendorLoginRequest;
use App\Http\Requests\Api\Vendor\VendorPhoneLoginRequest;
use App\Http\Requests\Api\Vendor\VendorRegisterRequest;
use App\Http\Requests\Api\Vendor\VendorResetPasswordRequest;
use App\Http\Requests\Api\Vendor\VendorSocialLoginRequest;
use App\Http\Resources\Vendor\VendorResource;
use App\Models\AppUser;
use App\Services\Vendor\VendorAuthService;
use App\Services\Vendor\VendorProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorAuthController extends Controller
{
    public function __construct(
        protected VendorAuthService $authService,
        protected VendorProfileService $profileService
    ) {
    }

    public function register(VendorRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        if (! empty($result['pending_approval']) || empty($result['token'])) {
            return ApiResponse::success([
                'pending_approval' => true,
                'vendor' => new VendorResource($result['user']),
            ], 'Registration successful. Your account is pending admin approval.');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new VendorResource($result['user']),
            'Registration successful',
            'vendor'
        );
    }

    public function login(VendorLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('fcmToken')
        );

        return $this->authResponse($result);
    }

    public function loginWithGoogle(VendorSocialLoginRequest $request): JsonResponse
    {
        return $this->socialLoginResponse(
            $this->authService->loginWithGoogle($request->input('id_token'), $request->validated())
        );
    }

    public function loginWithApple(VendorSocialLoginRequest $request): JsonResponse
    {
        return $this->socialLoginResponse(
            $this->authService->loginWithApple($request->input('id_token'), $request->validated())
        );
    }

    public function loginWithPhone(VendorPhoneLoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithPhone($request->validated());

        if (! empty($result['is_new_user']) && empty($result['token'])) {
            return ApiResponse::success(['is_new_user' => true, 'profile' => $result['profile'] ?? null], 'Registration required');
        }

        return $this->authResponse($result);
    }

    public function forgotPassword(VendorForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('email'));

        return ApiResponse::success(null, 'If a matching store account exists, a password reset email has been sent.');
    }

    public function resetPassword(VendorResetPasswordRequest $request): JsonResponse
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
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Logout successful');
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $this->authService->deleteAccount($request->user());

        return ApiResponse::success(null, 'Account deleted');
    }

    protected function authResponse(array $result): JsonResponse
    {
        $store = $this->profileService->getStore($result['user']);

        return ApiResponse::authSuccess(
            $result['token'],
            new VendorResource($result['user'], $store),
            'Login successful',
            'vendor'
        );
    }

    protected function socialLoginResponse(array $result): JsonResponse
    {
        if (! empty($result['is_new_user']) && empty($result['token'])) {
            return ApiResponse::success(['is_new_user' => true, 'profile' => $result['profile']], 'Registration required');
        }

        if (! empty($result['pending_approval']) || empty($result['token'])) {
            return ApiResponse::success([
                'pending_approval' => true,
                'vendor' => isset($result['user']) ? new VendorResource($result['user']) : null,
            ], 'Account pending approval');
        }

        return $this->authResponse($result);
    }
}
