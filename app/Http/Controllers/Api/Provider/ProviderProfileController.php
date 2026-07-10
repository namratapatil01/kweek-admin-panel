<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Provider\ProviderProfileUpdateRequest;
use App\Http\Resources\Provider\ProviderResource;
use App\Models\AppUser;
use App\Services\Provider\ProviderProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderProfileController extends Controller
{
    public function __construct(protected ProviderProfileService $profileService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(new ProviderResource($request->user()));
    }

    public function update(ProviderProfileUpdateRequest $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $updated = $this->profileService->update($user, $request->validated());

        return ApiResponse::success(new ProviderResource($updated), 'Profile updated');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $updated = $this->profileService->uploadProfileImage($user, $request->file('image'));

        return ApiResponse::success(new ProviderResource($updated), 'Profile image uploaded');
    }

    public function updateBankDetails(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bankName' => ['nullable', 'string', 'max:255'],
            'branchName' => ['nullable', 'string', 'max:255'],
            'holderName' => ['nullable', 'string', 'max:255'],
            'accountNumber' => ['nullable', 'string', 'max:64'],
            'otherDetails' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $updated = $this->profileService->updateBankDetails($user, $data);

        return ApiResponse::success(new ProviderResource($updated), 'Bank details updated');
    }
}
