<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Currency;

class CurrencyController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        // $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "currencies";
    }

    protected function buildDatatableRow($record): array
    {
        $config = $this->moduleConfig();
        $route = $this->routePrefix();
        $id = $record->id;
        $row = [];

        $row[] = $record->country ?? '';
        $row[] = $record->name ?? '';
        $row[] = $record->symbol ?? '';
        $row[] = $record->code ?? '';

        if ($record->symbolAtRight) {
            $row[] = '<span class="badge badge-success py-2 px-3 rounded-pill" style="background-color: #22c55e; color: #fff;">Yes</span>';
        } else {
            $row[] = '<span class="badge badge-danger py-2 px-3 rounded-pill" style="background-color: #ef4444; color: #fff;">No</span>';
        }

        $checked = $record->isActive ? 'checked' : '';
        $row[] = '<label class="switch"><input type="checkbox" class="toggle-status" data-id="' . e($id) . '" ' . $checked . '><span class="slider round"></span></label>';

        $btnStyle = 'display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; border: 1px solid; margin-right: 8px; text-decoration: none;';
        $actions = '<div class="d-flex align-items-center">';
        $actions .= '<a href="' . route('settings.currencies.edit', $id) . '" title="Edit" style="' . $btnStyle . ' color: #0284c7; border-color: #7dd3fc; background: #fff;"><i class="mdi mdi-lead-pencil" style="font-size: 16px;"></i></a>';
        $actions .= '<a href="javascript:void(0)" class="delete-row" data-id="' . e($id) . '" title="Delete" style="' . $btnStyle . ' color: #dc2626; border-color: #fca5a5; background: #fff;"><i class="mdi mdi-delete" style="font-size: 16px;"></i></a>';
        $actions .= '</div>';
        
        $row[] = $actions;

        return $row;
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|string',
            'isActive' => 'required|boolean',
        ]);
        
        $currency = Currency::findOrFail($request->id);
        $currency->isActive = $request->isActive;
        $currency->save();

        return response()->json(['success' => true]);
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value', '');
            $orderCol = (int) $request->input('order.0.column', 1);
            $orderDir = $request->input('order.0.dir', 'asc');

            $dataColumns = [
                0 => 'country',
                1 => 'name',
                2 => 'symbol',
                3 => 'code',
                4 => 'symbolAtRight',
                5 => 'isActive'
            ];

            $sortBy = $dataColumns[$orderCol] ?? 'name';

            $filters = array_filter([
                'search' => $search,
            ]);

            $result = $this->crudService()->datatable($filters, $start, $length, $sortBy, $orderDir);

            $rows = [];
            foreach ($result['items'] as $record) {
                $rows[] = $this->buildDatatableRow($record);
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $result['total'],
                'recordsFiltered' => $result['total'],
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(static::class . '@datatable', ['error' => $e->getMessage()]);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
