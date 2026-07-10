<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Http\Resources\Worker\WorkerResource;
use App\Models\AppUser;
use App\Services\Worker\WorkerProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerProfileController extends Controller
{
    public function __construct(protected WorkerProfileService $profileService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $worker = $this->profileService->getWorkerOrFail($user);

        return ApiResponse::success(new WorkerResource($worker));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['sometimes', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'fcmToken' => ['nullable', 'string'],
            'online' => ['nullable', 'boolean'],
            'profilePictureURL' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $worker = $this->profileService->update($user, $data);

        return ApiResponse::success(new WorkerResource($worker), 'Profile updated');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $worker = $this->profileService->uploadProfileImage($user, $request->file('image'));

        return ApiResponse::success(new WorkerResource($worker), 'Profile image uploaded');
    }

    public function setOnline(Request $request): JsonResponse
    {
        $data = $request->validate([
            'online' => ['required', 'boolean'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $worker = $this->profileService->setOnline($user, (bool) $data['online']);

        return ApiResponse::success(new WorkerResource($worker), 'Availability updated');
    }

    public function provider(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->profileService->providerInfo($user));
    }
}
