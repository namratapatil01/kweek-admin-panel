<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Provider\ProviderWorkerService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderWorkerController extends Controller
{
    public function __construct(protected ProviderWorkerService $workerService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $onlineOnly = $request->has('online') ? filter_var($request->input('online'), FILTER_VALIDATE_BOOLEAN) : null;

        return ApiResponse::paginated(
            $this->workerService->list($user->id, $onlineOnly, (int) $request->input('per_page', 20)),
            'Workers retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $worker = $this->workerService->show($user->id, $id);

        if (! $worker) {
            return ApiResponse::error('Worker not found', 404);
        }

        return ApiResponse::success($worker);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string'],
            'salary' => ['nullable', 'numeric'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'password' => ['nullable', 'string', 'min:6'],
            'active' => ['nullable', 'boolean'],
            'online' => ['nullable', 'boolean'],
            'profilePictureURL' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->workerService->create($user, $data),
            'Worker created',
            201
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['sometimes', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string'],
            'salary' => ['nullable', 'numeric'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'password' => ['nullable', 'string', 'min:6'],
            'active' => ['nullable', 'boolean'],
            'online' => ['nullable', 'boolean'],
            'profilePictureURL' => ['nullable', 'string'],
            'fcmToken' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $worker = $this->workerService->update($user->id, $id, $data);

        if (! $worker) {
            return ApiResponse::error('Worker not found', 404);
        }

        return ApiResponse::success($worker, 'Worker updated');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        if (! $this->workerService->delete($user->id, $id)) {
            return ApiResponse::error('Worker not found', 404);
        }

        return ApiResponse::success(null, 'Worker deleted');
    }

    public function uploadImage(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $worker = $this->workerService->uploadImage($user->id, $id, $request->file('image'));

        if (! $worker) {
            return ApiResponse::error('Worker not found', 404);
        }

        return ApiResponse::success($worker, 'Worker image uploaded');
    }
}
