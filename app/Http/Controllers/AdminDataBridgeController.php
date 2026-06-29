<?php

namespace App\Http\Controllers;

use App\Services\DocumentStoreService;
use App\Services\Storage\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDataBridgeController extends Controller
{
    public function getDocument(string $collection, string $id, DocumentStoreService $store): JsonResponse
    {
        return response()->json([
            'data' => $store->getDocument($collection, $id),
        ]);
    }

    public function query(Request $request, DocumentStoreService $store): JsonResponse
    {
        $validated = $request->validate([
            'collection' => ['required', 'string', 'max:120'],
            'filters' => ['sometimes', 'array'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'orderBy' => ['sometimes', 'nullable', 'string', 'max:120'],
            'orderDir' => ['sometimes', 'in:asc,desc'],
            'startAt' => ['sometimes', 'nullable', 'string'],
            'endAt' => ['sometimes', 'nullable', 'string'],
        ]);

        $documents = $store->queryForBridge(
            $validated['collection'],
            $validated['filters'] ?? [],
            (int) ($validated['limit'] ?? 500),
            $validated['orderBy'] ?? null,
            $validated['orderDir'] ?? 'desc',
            $validated['startAt'] ?? null,
            $validated['endAt'] ?? null
        );

        return response()->json(['data' => $documents]);
    }

    public function upsert(Request $request, DocumentStoreService $store): JsonResponse
    {
        $validated = $request->validate([
            'collection' => ['required', 'string', 'max:120'],
            'id' => ['required', 'string', 'max:128'],
            'data' => ['required', 'array'],
            'merge' => ['sometimes', 'boolean'],
        ]);

        if ($validated['merge'] ?? false) {
            $existing = $store->getDocument($validated['collection'], $validated['id']) ?? [];
            $validated['data'] = array_merge($existing, $validated['data']);
        }

        $result = $store->upsertDocument(
            $validated['collection'],
            $validated['id'],
            $validated['data']
        );

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    public function deleteDocument(string $collection, string $id, DocumentStoreService $store): JsonResponse
    {
        return response()->json([
            'success' => $store->deleteDocument($collection, $id),
        ]);
    }

    public function upload(Request $request, FileStorageService $storage): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
            'directory' => ['sometimes', 'string', 'max:120'],
        ]);

        $result = $storage->upload(
            $request->file('file'),
            $request->input('directory', 'images'),
            'public'
        );

        return response()->json([
            'success' => true,
            'url' => $result['url'],
            'path' => $result['path'],
        ]);
    }

    public function deleteFile(Request $request, FileStorageService $storage): JsonResponse
    {
        $request->validate([
            'url' => ['sometimes', 'nullable', 'string'],
            'path' => ['sometimes', 'nullable', 'string'],
        ]);

        $path = $request->input('path');
        if (! $path && $request->filled('url')) {
            $path = $this->pathFromUrl((string) $request->input('url'));
        }

        if (! $path) {
            return response()->json(['success' => false, 'message' => 'No file path provided.'], 422);
        }

        return response()->json(['success' => $storage->delete($path)]);
    }

    protected function pathFromUrl(string $url): ?string
    {
        $parsed = parse_url($url, PHP_URL_PATH);
        if (! is_string($parsed) || $parsed === '') {
            return null;
        }

        $storagePath = '/storage/';
        $pos = strpos($parsed, $storagePath);

        return $pos !== false ? ltrim(substr($parsed, $pos + strlen($storagePath)), '/') : ltrim($parsed, '/');
    }
}
