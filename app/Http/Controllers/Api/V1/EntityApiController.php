<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EntityIndexRequest;
use App\Http\Requests\Api\EntityStoreRequest;
use App\Http\Requests\Api\EntityUpdateRequest;
use App\Http\Resources\EntityResource;
use App\Services\EntityRegistry;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EntityApiController extends Controller
{
    public function index(EntityIndexRequest $request, EntityRegistry $registry, string $entity): AnonymousResourceCollection
    {
        $paginator = $registry->get($entity)->list(
            $request->filters(),
            $request->perPage(),
            $request->sortBy(),
            $request->sortDir()
        );

        return EntityResource::collection($paginator)->additional([
            'success' => true,
            'message' => 'Success',
        ]);
    }

    public function show(EntityRegistry $registry, string $entity, string $id): JsonResponse
    {
        return ApiResponse::success(new EntityResource($registry->get($entity)->show($id)));
    }

    public function store(EntityStoreRequest $request, EntityRegistry $registry, string $entity): JsonResponse
    {
        $model = $registry->get($entity)->store($request->validated());

        return ApiResponse::success(new EntityResource($model), 'Created', 201);
    }

    public function update(EntityUpdateRequest $request, EntityRegistry $registry, string $entity, string $id): JsonResponse
    {
        $model = $registry->get($entity)->update($id, $request->validated());

        return ApiResponse::success(new EntityResource($model), 'Updated');
    }

    public function destroy(EntityRegistry $registry, string $entity, string $id): JsonResponse
    {
        $registry->get($entity)->destroy($id);

        return ApiResponse::success(null, 'Deleted');
    }
}
