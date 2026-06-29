<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EntityIndexRequest;
use App\Http\Requests\Api\EntityStoreRequest;
use App\Http\Requests\Api\EntityUpdateRequest;
use App\Http\Resources\EntityResource;
use App\Services\EntityCrudService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class EntityController extends Controller
{
    public function __construct(protected EntityCrudService $service)
    {
    }

    public function index(EntityIndexRequest $request): JsonResponse
    {
        $paginator = $this->service->list(
            $request->filters(),
            $request->perPage(),
            $request->sortBy(),
            $request->sortDir()
        );

        return ApiResponse::paginated(EntityResource::collection($paginator)->response()->getData(true)['data'] ?? $paginator);
    }

    public function show(string $id): JsonResponse
    {
        return ApiResponse::success(new EntityResource($this->service->show($id)));
    }

    public function store(EntityStoreRequest $request): JsonResponse
    {
        $model = $this->service->store($request->validated());

        return ApiResponse::success(new EntityResource($model), 'Created', 201);
    }

    public function update(EntityUpdateRequest $request, string $id): JsonResponse
    {
        $model = $this->service->update($id, $request->validated());

        return ApiResponse::success(new EntityResource($model), 'Updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->destroy($id);

        return ApiResponse::success(null, 'Deleted');
    }
}
