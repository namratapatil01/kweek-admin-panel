<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DynamicNotificationController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "dynamic-notifications";
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value', '');
            $orderCol = (int) $request->input('order.0.column', 4); // Default sort by Date Created (column index 4)
            $orderDir = $request->input('order.0.dir', 'desc');

            $columnsMap = [
                0 => 'service_type',
                1 => 'type',
                2 => 'subject',
                3 => 'message',
                4 => 'created_at'
            ];

            $sortBy = $columnsMap[$orderCol] ?? 'created_at';

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

    protected function buildDatatableRow($record): array
    {
        $row = [];

        // 1. Service Type (Plain text format to match user screenshot)
        $serviceType = data_get($record, 'service_type');
        $row[] = e((string) ($serviceType ?? ''));

        // 2. Notification Type (Formatted nicely like the user screenshot)
        $notificationType = data_get($record, 'type');
        $formattedType = ucwords(str_replace(['_', '-'], ' ', (string) $notificationType));
        $row[] = e($formattedType);

        // 3. Subject
        $row[] = e((string) (data_get($record, 'subject') ?? ''));

        // 4. Message
        $row[] = e((string) (data_get($record, 'message') ?? ''));

        // 5. Date Created (Format: Mon Jul 31 2023 12:07:08 PM)
        $createdAt = $record->created_at ?: $record->createdAt;
        if ($createdAt) {
            $dateStr = Carbon::parse($createdAt)->format('D M d Y g:i:s A');
        } else {
            $dateStr = '';
        }
        $row[] = e($dateStr);

        // 6. Actions (Circular view & edit buttons matching screenshot structure)
        $route = $this->routePrefix();
        $id = $record->id;

        $viewUrl = route($route . '.show', $id);
        $editUrl = route($route . '.edit', $id);

        $actions = '<div class="action-btn-circle-container">';
        $actions .= '<a href="' . $viewUrl . '" class="btn-circle btn-circle-view" data-toggle="tooltip" title="View"><i class="fa fa-info"></i></a>';
        $actions .= '<a href="' . $editUrl . '" class="btn-circle btn-circle-edit" data-toggle="tooltip" title="Edit"><i class="mdi mdi-lead-pencil"></i></a>';
        $actions .= '</div>';
        
        $row[] = $actions;

        return $row;
    }
}
