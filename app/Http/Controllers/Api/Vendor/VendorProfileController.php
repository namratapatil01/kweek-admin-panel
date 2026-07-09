<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\VendorResource;
use App\Services\Vendor\VendorProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorProfileController extends Controller
{
    public function __construct(protected VendorProfileService $profileService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $store = $this->profileService->getStore($user);

        return ApiResponse::success(new VendorResource($user, $store));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['sometimes', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'fcmToken' => ['nullable', 'string'],
        ]);

        $user = $this->profileService->updateUser($request->user(), $data);
        $store = $this->profileService->getStore($user);

        return ApiResponse::success(new VendorResource($user, $store), 'Profile updated');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate(['image' => ['required', 'file', 'image', 'max:5120']]);
        $user = $this->profileService->uploadUserImage($request->user(), $request->file('image'));
        $store = $this->profileService->getStore($user);

        return ApiResponse::success(new VendorResource($user, $store), 'Profile image uploaded');
    }

    public function updateBankDetails(Request $request): JsonResponse
    {
        $data = $request->validate(['userBankDetails' => ['required', 'array']]);
        $user = $this->profileService->updateBankDetails($request->user(), $data['userBankDetails']);
        $store = $this->profileService->getStore($user);

        return ApiResponse::success(new VendorResource($user, $store), 'Bank details updated');
    }

    public function showStore(Request $request): JsonResponse
    {
        $store = $this->profileService->getStore($request->user());

        return ApiResponse::success($store?->toDocumentArray());
    }

    public function createStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categoryID' => ['nullable', 'string', 'max:64'],
            'categoryTitle' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'location' => ['nullable', 'string'],
            'zoneId' => ['nullable', 'string', 'max:64'],
            'phonenumber' => ['nullable', 'string', 'max:32'],
            'workingHours' => ['nullable', 'array'],
            'isSelfDelivery' => ['nullable', 'boolean'],
            'dine_in_active' => ['nullable', 'boolean'],
        ]);

        $store = $this->profileService->createStore($request->user(), $data);
        $user = $request->user()->fresh();

        return ApiResponse::success([
            'vendor' => new VendorResource($user, $store),
            'store' => $store->toDocumentArray(),
        ], 'Store created', 201);
    }

    public function updateStore(Request $request): JsonResponse
    {
        $data = $request->except(['_method']);
        $store = $this->profileService->updateStore($request->user(), $data);

        return ApiResponse::success($store->toDocumentArray(), 'Store updated');
    }

    public function uploadStoreImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
            'type' => ['nullable', 'string', 'in:photo,cover,photos'],
        ]);

        $store = $this->profileService->uploadStoreImage(
            $request->user(),
            $request->file('image'),
            $request->input('type', 'photo')
        );

        return ApiResponse::success($store->toDocumentArray(), 'Store image uploaded');
    }
}
