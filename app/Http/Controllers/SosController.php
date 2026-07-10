<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use App\Models\Ride;
use App\Models\Sos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SosController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'sos';
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = strtolower((string) $request->input('search.value', ''));
            $orderCol = (int) $request->input('order.0.column', 1);
            $orderDir = $request->input('order.0.dir', 'desc');
            $sectionId = $request->input('sectionId') ?: $request->cookie('section_id');

            $orderableColumns = ['orderId', 'id', 'userName', 'driverName', 'address', 'status'];
            $sortField = $orderableColumns[$orderCol] ?? 'id';

            $allSos = Sos::query()->orderByDesc('createdAt')->get();
            $orderIds = $allSos->pluck('orderId')->filter()->unique()->values()->all();
            $rides = Ride::query()->whereIn('id', $orderIds)->get()->keyBy('id');

            $rows = [];
            foreach ($allSos as $sos) {
                $orderId = $sos->orderId;
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

                $author = is_array($rideData['author'] ?? null) ? $rideData['author'] : [];
                $driver = is_array($rideData['driver'] ?? null) ? $rideData['driver'] : [];
                $userName = trim(($author['firstName'] ?? '') . ' ' . ($author['lastName'] ?? ''));
                $driverName = trim(($driver['firstName'] ?? '') . ' ' . ($driver['lastName'] ?? ''));

                $item = [
                    'id' => $sos->id,
                    'orderId' => $orderId,
                    'status' => (string) ($sos->status ?? ''),
                    'userName' => $userName,
                    'driverName' => $driverName,
                    'address' => (string) ($rideData['destinationLocationName'] ?? ''),
                    'userid' => (string) ($author['id'] ?? ''),
                    'driverid' => (string) ($driver['id'] ?? ''),
                ];

                if ($search !== '') {
                    $haystack = strtolower(implode(' ', array_filter([
                        $item['id'],
                        $item['orderId'],
                        $item['status'],
                        $item['userName'],
                        $item['driverName'],
                        $item['address'],
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
            $formatted = array_map(fn (array $item) => $this->buildSosDatatableRow($item), $page);

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

    protected function buildSosDatatableRow(array $item): array
    {
        $id = $item['id'];
        $orderId = $item['orderId'];
        $editRoute = route('sos.edit', $id);
        $rideRoute = route('rides.edit', $orderId);

        $orderLink = '<a href="' . e($rideRoute) . '" data-toggle="tooltip" title="' . e($orderId) . '">'
            . e(strlen($orderId) > 8 ? substr($orderId, 0, 8) . '...' : $orderId) . '</a>';

        $sosLink = '<a href="' . e($editRoute) . '" data-toggle="tooltip" title="' . e($id) . '">'
            . e(strlen($id) > 8 ? substr($id, 0, 8) . '...' : $id) . '</a>';

        $client = $item['userid']
            ? '<a href="' . e(route('users.view', $item['userid'])) . '">' . e($item['userName']) . '</a>'
            : '';

        $driver = $item['driverid']
            ? '<a href="' . e(route('drivers.view', $item['driverid'])) . '">' . e($item['driverName']) . '</a>'
            : '';

        $status = $item['status'];
        if ($status === 'Completed') {
            $statusBadge = '<span class="badge badge-success">' . e($status) . '</span>';
        } elseif ($status === 'Processing') {
            $statusBadge = '<span class="badge badge-info">' . e($status) . '</span>';
        } else {
            $statusBadge = '<span class="badge badge-primary">' . e($status) . '</span>';
        }

        $actions = '<span class="action-btn">';
        $actions .= '<a href="' . e($editRoute) . '" data-toggle="tooltip" title="' . e(trans('lang.edit')) . '"><i class="mdi mdi-lead-pencil"></i></a>';
        if ($this->userCanDeleteSos()) {
            $actions .= '<a href="javascript:void(0)" class="delete-sos" data-id="' . e($id) . '" data-toggle="tooltip" title="' . e(trans('lang.delete')) . '"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';

        return [
            $orderLink,
            $sosLink,
            $client,
            $driver,
            e($item['address']),
            $statusBadge,
            $actions,
        ];
    }

    protected function userCanDeleteSos(): bool
    {
        $user = auth()->user();
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];

        return in_array('sos.rides.delete', $permissions, true);
    }
}
