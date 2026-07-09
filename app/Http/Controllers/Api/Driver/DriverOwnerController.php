<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Http\Resources\Driver\DriverResource;
use App\Models\AppUser;
use App\Services\Driver\DriverOwnerService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverOwnerController extends Controller
{
    public function __construct(protected DriverOwnerService $ownerService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->ownerService->listDrivers($user, (int) $request->input('per_page', 20)),
            'Fleet drivers retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $driver = $this->ownerService->showDriver($user, $id);

        if (! $driver) {
            return ApiResponse::error('Fleet driver not found', 404);
        }

        return ApiResponse::success($driver);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'password' => ['nullable', 'string', 'min:8'],
            'carName' => ['nullable', 'string', 'max:120'],
            'carNumber' => ['nullable', 'string', 'max:64'],
            'vehicleType' => ['nullable', 'string', 'max:64'],
            'vehicleId' => ['nullable', 'string', 'max:64'],
            'rideType' => ['nullable', 'string', 'max:64'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $result = $this->ownerService->createDriver($user, $data);

        return ApiResponse::success($result, 'Fleet driver created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['sometimes', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'active' => ['nullable', 'boolean'],
            'carName' => ['nullable', 'string', 'max:120'],
            'carNumber' => ['nullable', 'string', 'max:64'],
            'vehicleType' => ['nullable', 'string', 'max:64'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $driver = $this->ownerService->updateDriver($user, $id, $data);

        return ApiResponse::success(new DriverResource($driver), 'Fleet driver updated');
    }

    public function uploadImage(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $driver = $this->ownerService->uploadDriverImage($user, $id, $request->file('image'));

        return ApiResponse::success(new DriverResource($driver), 'Fleet driver image uploaded');
    }
}
