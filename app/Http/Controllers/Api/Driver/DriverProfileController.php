<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Http\Resources\Driver\DriverResource;
use App\Models\AppUser;
use App\Services\Driver\DriverProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverProfileController extends Controller
{
    public function __construct(protected DriverProfileService $profileService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(new DriverResource($user));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['sometimes', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'countryCode' => ['nullable', 'string', 'max:8'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'section_id' => ['nullable', 'string', 'max:64'],
            'zoneId' => ['nullable', 'string', 'max:64'],
            'vendorID' => ['nullable', 'string', 'max:64'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'rotation' => ['nullable', 'numeric'],
            'fcmToken' => ['nullable', 'string'],
            'carName' => ['nullable', 'string', 'max:120'],
            'carNumber' => ['nullable', 'string', 'max:64'],
            'carMakes' => ['nullable', 'string', 'max:120'],
            'vehicleType' => ['nullable', 'string', 'max:64'],
            'vehicleId' => ['nullable', 'string', 'max:64'],
            'rideType' => ['nullable', 'string', 'max:64'],
            'userBankDetails' => ['nullable', 'array'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $driver = $this->profileService->update($user, $data);

        return ApiResponse::success(new DriverResource($driver), 'Profile updated');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $driver = $this->profileService->uploadProfileImage($user, $request->file('image'));

        return ApiResponse::success(new DriverResource($driver), 'Profile image uploaded');
    }

    public function setOnline(Request $request): JsonResponse
    {
        $data = $request->validate([
            'online' => ['required', 'boolean'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $driver = $this->profileService->setOnline($user, (bool) $data['online']);

        return ApiResponse::success(new DriverResource($driver), 'Availability updated');
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'rotation' => ['nullable', 'numeric'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $driver = $this->profileService->updateLocation($user, $data);

        return ApiResponse::success(new DriverResource($driver), 'Location updated');
    }

    public function updateBankDetails(Request $request): JsonResponse
    {
        $data = $request->validate([
            'userBankDetails' => ['required', 'array'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $driver = $this->profileService->updateBankDetails($user, $data['userBankDetails']);

        return ApiResponse::success(new DriverResource($driver), 'Bank details updated');
    }
}
