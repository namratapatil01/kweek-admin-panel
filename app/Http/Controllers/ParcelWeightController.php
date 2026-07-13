<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use App\Models\ParcelWeight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ParcelWeightController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "parcel-weights";
    }

    public function legacyIndex(): View
    {
        return view('parcel_weight.index');
    }

    public function records(): JsonResponse
    {
        $rows = ParcelWeight::query()
            ->orderByRaw('COALESCE(created_at, createdAt) ASC')
            ->get()
            ->map(fn (ParcelWeight $weight) => [
                'id' => $weight->id,
                'title' => (string) ($weight->title ?? ''),
                'delivery_charge' => (string) ($weight->delivery_charge ?? data_get($weight->payload, 'delivery_charge', '')),
            ])
            ->values();

        return response()->json(['data' => $rows]);
    }

    public function saveRecord(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|string|max:64',
            'title' => 'required|string|max:255',
            'delivery_charge' => 'required',
        ]);

        $id = (string) ($validated['id'] ?? Str::uuid());
        $payload = [
            'id' => $id,
            'title' => $validated['title'],
            'delivery_charge' => $validated['delivery_charge'],
        ];

        if ($request->filled('id') && ParcelWeight::query()->where('id', $id)->exists()) {
            $this->crudService()->update($id, $payload);
        } else {
            $this->crudService()->store($payload);
        }

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function bulkSave(Request $request): JsonResponse
    {
        $items = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|string|max:64',
            'items.*.title' => 'required|string|max:255',
            'items.*.delivery_charge' => 'required',
        ])['items'];

        foreach ($items as $item) {
            $payload = [
                'id' => $item['id'],
                'title' => $item['title'],
                'delivery_charge' => $item['delivery_charge'],
            ];

            if (ParcelWeight::query()->where('id', $item['id'])->exists()) {
                $this->crudService()->update($item['id'], $payload);
            } else {
                $this->crudService()->store($payload);
            }
        }

        return response()->json(['success' => true]);
    }

    public function deleteRecord(string $id): JsonResponse
    {
        $this->crudService()->destroy($id);

        return response()->json(['success' => true]);
    }
}
