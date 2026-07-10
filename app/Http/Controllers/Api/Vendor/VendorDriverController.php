<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorDriverService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorDriverController extends Controller
{
    public function __construct(protected VendorDriverService $driverService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->driverService->list($request->user(), (int) $request->input('per_page', 20)),
            'Drivers retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $driver = $this->driverService->show($request->user(), $id);
        if (! $driver) {
            return ApiResponse::error('Driver not found', 404);
        }

        return ApiResponse::success($driver);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'password' => ['nullable', 'string', 'min:8'],
            'carName' => ['nullable', 'string'],
            'carNumber' => ['nullable', 'string'],
            'vehicleType' => ['nullable', 'string'],
        ]);

        return ApiResponse::success($this->driverService->create($request->user(), $data), 'Driver created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $driver = $this->driverService->update($request->user(), $id, $request->all());

        return ApiResponse::success($driver->toDocumentArray(), 'Driver updated');
    }

    public function uploadImage(Request $request, string $id): JsonResponse
    {
        $request->validate(['image' => ['required', 'file', 'image', 'max:5120']]);
        $driver = $this->driverService->uploadImage($request->user(), $id, $request->file('image'));

        return ApiResponse::success($driver->toDocumentArray(), 'Driver image uploaded');
    }
}
