<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Provider\ProviderServiceRequest;
use App\Models\AppUser;
use App\Services\Provider\ProviderServiceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderServiceController extends Controller
{
    public function __construct(protected ProviderServiceService $serviceService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->serviceService->list($user->id, (int) $request->input('per_page', 20)),
            'Services retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $service = $this->serviceService->show($user->id, $id);

        if (! $service) {
            return ApiResponse::error('Service not found', 404);
        }

        return ApiResponse::success($service);
    }

    public function store(ProviderServiceRequest $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $service = $this->serviceService->create($user, $request->validated());

        return ApiResponse::success($service, 'Service created', 201);
    }

    public function update(ProviderServiceRequest $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $service = $this->serviceService->update($user->id, $id, $request->validated());

        if (! $service) {
            return ApiResponse::error('Service not found', 404);
        }

        return ApiResponse::success($service, 'Service updated');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        if (! $this->serviceService->delete($user->id, $id)) {
            return ApiResponse::error('Service not found', 404);
        }

        return ApiResponse::success(null, 'Service deleted');
    }

    public function uploadImages(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['file', 'image', 'max:5120'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $service = $this->serviceService->uploadImages($user->id, $id, $request->file('images'));

        if (! $service) {
            return ApiResponse::error('Service not found', 404);
        }

        return ApiResponse::success($service, 'Service images uploaded');
    }

    public function categories(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->serviceService->categories(
                $request->input('sectionId') ?? $request->input('section_id') ?? $request->user()?->sectionId,
                $request->input('parentCategoryId'),
                (int) $request->input('per_page', 50)
            ),
            'Categories retrieved'
        );
    }

    public function sections(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->serviceService->sections((int) $request->input('per_page', 50)),
            'Sections retrieved'
        );
    }
}
