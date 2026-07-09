<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\CustomerProfileUpdateRequest;
use App\Http\Resources\Customer\CustomerResource;
use App\Models\AppUser;
use App\Services\Customer\CustomerProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    public function __construct(protected CustomerProfileService $profileService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(new CustomerResource($request->user()));
    }

    public function update(CustomerProfileUpdateRequest $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $updated = $this->profileService->update($user, $request->validated());

        return ApiResponse::success(new CustomerResource($updated), 'Profile updated');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $updated = $this->profileService->uploadProfileImage($user, $request->file('image'));

        return ApiResponse::success(new CustomerResource($updated), 'Profile image uploaded');
    }
}
