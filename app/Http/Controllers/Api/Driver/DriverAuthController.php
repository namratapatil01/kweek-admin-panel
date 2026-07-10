<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Driver\DriverForgotPasswordRequest;
use App\Http\Requests\Api\Driver\DriverLoginRequest;
use App\Http\Requests\Api\Driver\DriverPhoneLoginRequest;
use App\Http\Requests\Api\Driver\DriverRegisterRequest;
use App\Http\Requests\Api\Driver\DriverResetPasswordRequest;
use App\Http\Requests\Api\Driver\DriverSocialLoginRequest;
use App\Http\Resources\Driver\DriverResource;
use App\Models\AppUser;
use App\Services\Driver\DriverAuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverAuthController extends Controller
{
    public function __construct(protected DriverAuthService $authService)
    {
    }

    public function register(DriverRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        if (! empty($result['pending_approval']) || empty($result['token'])) {
            return ApiResponse::success([
                'pending_approval' => true,
                'driver' => new DriverResource($result['user']),
            ], 'Registration successful. Your account is pending admin approval.');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new DriverResource($result['user']),
            'Registration successful',
            'driver'
        );
    }

    public function login(DriverLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('fcmToken')
        );

        return ApiResponse::authSuccess(
            $result['token'],
            new DriverResource($result['user']),
            'Login successful',
            'driver'
        );
    }

    public function loginWithGoogle(DriverSocialLoginRequest $request): JsonResponse
    {
        return $this->socialLoginResponse(
            $this->authService->loginWithGoogle(
                $request->input('id_token'),
                $request->validated()
            )
        );
    }

    public function loginWithApple(DriverSocialLoginRequest $request): JsonResponse
    {
        return $this->socialLoginResponse(
            $this->authService->loginWithApple(
                $request->input('id_token'),
                $request->validated()
            )
        );
    }

    public function loginWithPhone(DriverPhoneLoginRequest $request): JsonResponse
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
                'driver' => isset($result['user']) ? new DriverResource($result['user']) : null,
            ], 'Account pending approval');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new DriverResource($result['user']),
            'Login successful',
            'driver'
        );
    }

    public function sendPhoneOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phoneNumber' => ['required', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
        ]);

        return ApiResponse::success(
            $this->authService->sendPhoneOtp($data['phoneNumber'], $data['countryCode'] ?? null),
            'OTP sent'
        );
    }

    public function verifyPhoneOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'verificationId' => ['required', 'uuid'],
            'otp' => ['required', 'string', 'size:6'],
            'phoneNumber' => ['required', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'fcmToken' => ['nullable', 'string'],
            'serviceType' => ['nullable', 'string'],
        ]);

        $result = $this->authService->verifyPhoneOtpAndLogin($data);

        if (! empty($result['pending_approval']) || empty($result['token'])) {
            return ApiResponse::success([
                'pending_approval' => true,
                'driver' => isset($result['user']) ? new DriverResource($result['user']) : null,
            ], 'Account pending approval');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new DriverResource($result['user']),
            'Login successful',
            'driver'
        );
    }

    public function forgotPassword(DriverForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('email'));

        return ApiResponse::success(
            null,
            'If a matching driver account exists, a password reset email has been sent.'
        );
    }

    public function resetPassword(DriverResetPasswordRequest $request): JsonResponse
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
                'driver' => isset($result['user']) ? new DriverResource($result['user']) : null,
            ], 'Account pending approval');
        }

        return ApiResponse::authSuccess(
            $result['token'],
            new DriverResource($result['user']),
            'Login successful',
            'driver'
        );
    }
}
