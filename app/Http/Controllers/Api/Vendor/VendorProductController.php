<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorProductService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorProductController extends Controller
{
    public function __construct(protected VendorProductService $productService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->productService->list($request->user(), (int) $request->input('per_page', 20)),
            'Products retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $product = $this->productService->show($request->user(), $id);
        if (! $product) {
            return ApiResponse::error('Product not found', 404);
        }

        return ApiResponse::success($product);
    }

    public function store(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->productService->create($request->user(), $request->all()),
            'Product created',
            201
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = $this->productService->update($request->user(), $id, $request->all());
        if (! $product) {
            return ApiResponse::error('Product not found', 404);
        }

        return ApiResponse::success($product, 'Product updated');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (! $this->productService->delete($request->user(), $id)) {
            return ApiResponse::error('Product not found', 404);
        }

        return ApiResponse::success(null, 'Product deleted');
    }

    public function uploadImages(Request $request, string $id): JsonResponse
    {
        $request->validate(['images' => ['required', 'array'], 'images.*' => ['file', 'image', 'max:5120']]);
        $product = $this->productService->uploadImages($request->user(), $id, $request->file('images', []));
        if (! $product) {
            return ApiResponse::error('Product not found', 404);
        }

        return ApiResponse::success($product, 'Images uploaded');
    }
}
