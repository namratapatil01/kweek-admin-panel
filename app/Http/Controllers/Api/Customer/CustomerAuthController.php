<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\CustomerForgotPasswordRequest;
use App\Http\Requests\Api\Customer\CustomerLoginRequest;
use App\Http\Requests\Api\Customer\CustomerPhoneLoginRequest;
use App\Http\Requests\Api\Customer\CustomerRegisterRequest;
use App\Http\Requests\Api\Customer\CustomerResetPasswordRequest;
use App\Http\Requests\Api\Customer\CustomerSocialLoginRequest;
use App\Http\Resources\Customer\CustomerResource;
use App\Models\AppUser;
use App\Services\Customer\CustomerAuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerAuthController extends Controller
{
    public function __construct(protected CustomerAuthService $authService)
    {
    }

    public function register(CustomerRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::authSuccess(
            $result['token'],
            new CustomerResource($result['user']),
            'Registration successful'
        );
    }

    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('fcmToken')
        );

        return ApiResponse::authSuccess(
            $result['token'],
            new CustomerResource($result['user']),
            'Login successful'
        );
    }

    public function loginWithGoogle(CustomerSocialLoginRequest $request): JsonResponse
    {
        return $this->socialLoginResponse(
            $this->authService->loginWithGoogle(
                $request->input('id_token'),
                $request->validated()
            )
        );
    }

    public function loginWithApple(CustomerSocialLoginRequest $request): JsonResponse
    {
        return $this->socialLoginResponse(
            $this->authService->loginWithApple(
                $request->input('id_token'),
                $request->validated()
            )
        );
    }

    public function loginWithPhone(CustomerPhoneLoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithPhone($request->validated());

        if (! empty($result['is_new_user']) && empty($result['token'])) {
            return ApiResponse::success([
                'is_new_user' => true,
                'profile' => $result['profile'] ?? null,
            ], 'Registration required');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new CustomerResource($result['user']),
            'Login successful'
        );
    }

    public function forgotPassword(CustomerForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('email'));

        return ApiResponse::success(
            null,
            'If a matching customer account exists, a password reset email has been sent.'
        );
    }

    public function resetPassword(CustomerResetPasswordRequest $request): JsonResponse
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

        return ApiResponse::success(null, 'Account deleted successfully');
    }

    protected function socialLoginResponse(array $result): JsonResponse
    {
        if (! empty($result['is_new_user'])) {
            return ApiResponse::success([
                'is_new_user' => true,
                'profile' => $result['profile'],
            ], 'Registration required');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new CustomerResource($result['user']),
            'Login successful'
        );
    }
}
