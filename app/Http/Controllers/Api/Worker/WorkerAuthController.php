<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Worker\WorkerForgotPasswordRequest;
use App\Http\Requests\Api\Worker\WorkerLoginRequest;
use App\Http\Requests\Api\Worker\WorkerRegisterRequest;
use App\Http\Requests\Api\Worker\WorkerResetPasswordRequest;
use App\Http\Resources\Worker\WorkerResource;
use App\Models\AppUser;
use App\Services\Worker\WorkerAuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerAuthController extends Controller
{
    public function __construct(protected WorkerAuthService $authService)
    {
    }

    public function register(WorkerRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::authSuccess(
            $result['token'],
            new WorkerResource($result['worker']),
            'Registration successful',
            'worker'
        );
    }

    public function login(WorkerLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('fcmToken')
        );

        return ApiResponse::authSuccess(
            $result['token'],
            new WorkerResource($result['worker']),
            'Login successful',
            'worker'
        );
    }

    public function forgotPassword(WorkerForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('email'));

        return ApiResponse::success(
            null,
            'If a matching worker account exists, a password reset email has been sent.'
        );
    }

    public function resetPassword(WorkerResetPasswordRequest $request): JsonResponse
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
}
