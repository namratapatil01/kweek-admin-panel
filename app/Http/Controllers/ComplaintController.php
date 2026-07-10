<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use App\Models\Complaint;
use App\Models\Ride;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ComplaintController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'complaints';
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = strtolower((string) $request->input('search.value', ''));
            $orderCol = (int) $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'asc');
            $sectionId = $request->input('sectionId') ?: $request->cookie('section_id');

            $orderableColumns = ['orderId', 'title', 'description', 'customerName', 'driverName', 'status'];
            $sortField = $orderableColumns[$orderCol] ?? 'orderId';

            $allComplaints = Complaint::query()->orderByDesc('createdAt')->get();
            $orderIds = $allComplaints->pluck('orderId')->filter()->unique()->values()->all();
            $rides = Ride::query()->whereIn('id', $orderIds)->get()->keyBy('id');

            $rows = [];
            foreach ($allComplaints as $complaint) {
                $orderId = $complaint->orderId;
                if (! $orderId) {
                    continue;
                }

                $ride = $rides->get($orderId);
                if (! $ride) {
                    continue;
                }

                $rideData = $ride->toDocumentArray();
                $rideSectionId = $rideData['sectionId'] ?? $rideData['section_id'] ?? null;
                if ($sectionId && $sectionId !== 'all' && $rideSectionId && (string) $rideSectionId !== (string) $sectionId) {
                    continue;
                }

                $item = [
                    'id' => $complaint->id,
                    'orderId' => $orderId,
                    'title' => (string) ($complaint->title ?? ''),
                    'description' => (string) ($complaint->description ?? ''),
                    'customerName' => (string) ($complaint->customerName ?? ''),
                    'driverName' => (string) ($complaint->driverName ?? ''),
                    'customerId' => (string) ($complaint->customerId ?? ''),
                    'driverId' => (string) ($complaint->driverId ?? ''),
                    'status' => (string) ($complaint->status ?? ''),
                ];

                if ($search !== '') {
                    $haystack = strtolower(implode(' ', array_filter([
                        $item['orderId'],
                        $item['title'],
                        $item['description'],
                        $item['customerName'],
                        $item['driverName'],
                        $item['status'],
                    ])));

                    if (! str_contains($haystack, $search)) {
                        continue;
                    }
                }

                $rows[] = $item;
            }

            usort($rows, function (array $a, array $b) use ($sortField, $orderDir) {
                $aVal = strtolower((string) ($a[$sortField] ?? ''));
                $bVal = strtolower((string) ($b[$sortField] ?? ''));
                $cmp = $aVal <=> $bVal;

                return $orderDir === 'asc' ? $cmp : -$cmp;
            });

            $total = count($rows);
            $page = array_slice($rows, $start, $length > 0 ? $length : 10);
            $formatted = array_map(fn (array $item) => $this->buildComplaintDatatableRow($item), $page);

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $formatted,
            ]);
        } catch (Throwable $e) {
            Log::error(static::class . '@datatable', ['error' => $e->getMessage()]);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function buildComplaintDatatableRow(array $item): array
    {
        $id = $item['id'];
        $orderId = $item['orderId'];
        $editRoute = route('complaints.edit', $id);
        $rideRoute = route('rides.edit', $orderId);

        $orderLink = '<a href="' . e($rideRoute) . '" data-toggle="tooltip" title="' . e($orderId) . '">'
            . e(strlen($orderId) > 8 ? substr($orderId, 0, 8) . '...' : $orderId) . '</a>';

        $customer = $item['customerId']
            ? '<a href="' . e(route('users.view', $item['customerId'])) . '">' . e($item['customerName']) . '</a>'
            : e($item['customerName']);

        $driver = $item['driverId']
            ? '<a href="' . e(route('drivers.view', $item['driverId'])) . '">' . e($item['driverName']) . '</a>'
            : e($item['driverName']);

        $status = $item['status'];
        if ($status === 'Resolved') {
            $statusBadge = '<span class="badge badge-success">' . e($status) . '</span>';
        } elseif ($status === 'Under Investigation') {
            $statusBadge = '<span class="badge badge-warning">' . e($status) . '</span>';
        } else {
            $statusBadge = '<span class="badge badge-primary">' . e($status) . '</span>';
        }

        $actions = '<span class="action-btn">';
        $actions .= '<a href="' . e($editRoute) . '" data-toggle="tooltip" title="' . e(trans('lang.edit')) . '"><i class="mdi mdi-lead-pencil"></i></a>';
        if ($this->userCanDeleteComplaint()) {
            $actions .= '<a href="javascript:void(0)" class="delete-complaint" data-id="' . e($id) . '" data-toggle="tooltip" title="' . e(trans('lang.delete')) . '"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';

        return [
            $orderLink,
            e($item['title']),
            e($item['description']),
            $customer,
            $driver,
            $statusBadge,
            $actions,
        ];
    }

    protected function userCanDeleteComplaint(): bool
    {
        $user = auth()->user();
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];

        return in_array('complaints.delete', $permissions, true);
    }
}
