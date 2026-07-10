<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutRequestController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id="")
    {
        return view("payoutRequests.drivers.index")->with("id",$id);
    }

    public function vendor($id="")
    {
        return view("payoutRequests.vendor.index")->with("id",$id);
    }
    public function provider($id = "")
    {
        return view("payoutRequests.provider.index")->with("id", $id);
    }
    public function owner($id = "")
    {
        return view("payoutRequests.owner.index")->with("id", $id);
    }
    public function vendorDisbursements($id = '')
    {
        $query = DB::table('payouts');
        if ($id) {
            $query->where('vendorID', $id);
        }

        return view('payoutRequests.vendor.disbursement_index', [
            'id' => $id,
            'totalPayouts' => $query->count(),
        ]);
    }
    public function driverDisbursements($id = '')
    {
        $query = DB::table('driver_payouts')
            ->leftJoin('app_users', 'driver_payouts.driverID', '=', 'app_users.id')
            ->where(function ($q) {
                $q->whereNull('app_users.isOwner')->orWhere('app_users.isOwner', false);
            });

        if ($id) {
            $query->where('driver_payouts.driverID', $id);
        }

        return view('payoutRequests.drivers.disbursement_index', [
            'id' => $id,
            'totalPayouts' => $query->count(),
        ]);
    }
    public function providerDisbursements($id = "")
    {
        return view("payoutRequests.provider.disbursement_index")->with("id", $id);
    }
    public function ownerDisbursements($id="")
    {
        return view("payoutRequests.owner.disbursement_index")->with("id",$id);
    }

    public function datatableDriver(Request $request)
    {
        try {
            $driverId = $request->input('driver_id', '');

            $draw    = intval($request->input('draw', 1));
            $start   = intval($request->input('start', 0));
            $length  = intval($request->input('length', 10));
            $search  = $request->input('search.value', '');
            $orderCol = intval($request->input('order.0.column', 4));
            $orderDir = $request->input('order.0.dir', 'desc');

            $query = \Illuminate\Support\Facades\DB::table('driver_payouts')
                ->leftJoin('app_users', 'driver_payouts.driverID', '=', 'app_users.id')
                ->select(
                    'driver_payouts.*',
                    'app_users.firstName',
                    'app_users.lastName'
                )
                ->where('driver_payouts.paymentStatus', 'Pending');

            if ($driverId) {
                $query->where('driver_payouts.driverID', $driverId);
            }

            $totalCount = $query->count();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('driver_payouts.amount', 'LIKE', "%{$search}%")
                      ->orWhere('driver_payouts.note', 'LIKE', "%{$search}%")
                      ->orWhere('app_users.firstName', 'LIKE', "%{$search}%")
                      ->orWhere('app_users.lastName', 'LIKE', "%{$search}%");
                });
            }

            $totalFiltered = $query->count();

            if ($driverId) {
                $columns = ['', '', 'driver_payouts.amount', 'driver_payouts.note', 'driver_payouts.created_at', 'driver_payouts.paymentStatus', 'driver_payouts.withdrawMethod', ''];
            } else {
                $columns = ['', 'app_users.firstName', 'driver_payouts.amount', 'driver_payouts.note', 'driver_payouts.created_at', 'driver_payouts.paymentStatus', 'driver_payouts.withdrawMethod', ''];
            }

            $orderByField = $columns[$orderCol] ?? 'driver_payouts.created_at';
            $query->orderBy($orderByField ?: 'driver_payouts.created_at', $orderDir);

            $payouts = $query->skip($start)->take($length)->get();

            $currency = \Illuminate\Support\Facades\DB::table('currencies')->where('isActive', 1)->first();
            $currencySymbol = $currency->symbol ?? '$';
            $symbolAtRight = (bool)($currency->symbolAtRight ?? false);
            $decimal_digits = $currency->decimal_degits ?? 2;

            $data = [];
            foreach ($payouts as $payout) {
                $row = (array)$payout;
                
                $row['recid'] = $payout->id;
                if (!$driverId) {
                    $row['title'] = trim($payout->firstName . ' ' . $payout->lastName);
                } else {
                    $row['title'] = trim($payout->firstName . ' ' . $payout->lastName);
                }
                
                $amount = number_format($payout->amount, $decimal_digits);
                if ($symbolAtRight) {
                    $row['amount_formatted'] = $amount . $currencySymbol;
                } else {
                    $row['amount_formatted'] = $currencySymbol . $amount;
                }
                
                $row['paidDate'] = $payout->created_at ? \Carbon\Carbon::parse($payout->created_at)->toIso8601String() : null;
                
                $data[] = $row;
            }

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $totalCount,
                'recordsFiltered' => $totalFiltered,
                'data'            => $data,
                'filteredData'    => $data,
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PayoutRequestController@datatableDriver error: ' . $e->getMessage());
            return response()->json([
                'draw'            => intval($request->input('draw', 1)),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => $e->getMessage()
            ], 500);
        }
    }

    public function acceptDriverRequest(Request $request)
    {
        try {
            $id = $request->input('id');
            \Illuminate\Support\Facades\DB::table('driver_payouts')->where('id', $id)->update(['paymentStatus' => 'Success', 'updated_at' => now()]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PayoutRequestController@acceptDriverRequest error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cancelDriverRequest(Request $request)
    {
        try {
            $id = $request->input('id');
            $adminNote = $request->input('admin_note', '');
            
            // Get payout info
            $payout = \Illuminate\Support\Facades\DB::table('driver_payouts')->where('id', $id)->first();
            if ($payout) {
                // Return amount to wallet
                \App\Models\AppUser::where('id', $payout->driverID)->increment('wallet_amount', $payout->amount);
                
                \Illuminate\Support\Facades\DB::table('driver_payouts')->where('id', $id)->update([
                    'paymentStatus' => 'Reject',
                    'note' => $adminNote,
                    'updated_at' => now()
                ]);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PayoutRequestController@cancelDriverRequest error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function datatableVendorDisbursement(Request $request): JsonResponse
    {
        try {
            $vendorId = $request->input('vendor_id');
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = trim($request->input('search.value', ''));
            $orderCol = (int) $request->input('order.0.column', 4);
            $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
            $hasVendorColumn = ! $vendorId;

            $query = DB::table('payouts')
                ->leftJoin('vendors', 'payouts.vendorID', '=', 'vendors.id')
                ->select('payouts.*', 'vendors.title as vendor_title');

            if ($vendorId) {
                $query->where('payouts.vendorID', $vendorId);
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('vendors.title', 'like', "%{$search}%")
                        ->orWhere('payouts.amount', 'like', "%{$search}%")
                        ->orWhere('payouts.note', 'like', "%{$search}%")
                        ->orWhere('payouts.paymentStatus', 'like', "%{$search}%")
                        ->orWhere('payouts.withdrawMethod', 'like', "%{$search}%");
                });
            }

            $total = (clone $query)->count();

            $columns = $hasVendorColumn
                ? ['', 'vendors.title', 'payouts.amount', 'payouts.note', 'payouts.paidDate', 'payouts.paymentStatus', 'payouts.withdrawMethod', '']
                : ['', 'payouts.amount', 'payouts.note', 'payouts.paidDate', 'payouts.paymentStatus', 'payouts.withdrawMethod', ''];
            $orderBy = $columns[$orderCol] ?? 'payouts.paidDate';
            if ($orderBy === '') {
                $orderBy = 'payouts.paidDate';
            }

            $payouts = $query
                ->orderBy($orderBy, $orderDir)
                ->skip($start)
                ->take($length > 0 ? $length : 10)
                ->get();

            $currency = DB::table('currencies')->where('isActive', 1)->first();
            $currencySymbol = $currency->symbol ?? '₱';
            $symbolAtRight = (bool) ($currency->symbolAtRight ?? false);
            $decimalDigits = (int) ($currency->decimal_degits ?? 2);

            $rows = [];
            foreach ($payouts as $payout) {
                $rows[] = $this->buildVendorDisbursementRow($payout, $hasVendorColumn, $currencySymbol, $symbolAtRight, $decimalDigits);
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            Log::error('PayoutRequestController@datatableVendorDisbursement', ['error' => $e->getMessage()]);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroyVendorPayout(Request $request): JsonResponse
    {
        try {
            $ids = array_values(array_filter(array_unique(array_merge(
                (array) $request->input('ids', []),
                $request->filled('id') ? [$request->input('id')] : []
            ))));

            if ($ids !== []) {
                DB::table('payouts')->whereIn('id', $ids)->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('PayoutRequestController@destroyVendorPayout', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    protected function buildVendorDisbursementRow(object $payout, bool $hasVendorColumn, string $currencySymbol, bool $symbolAtRight, int $decimalDigits): array
    {
        $price = number_format((float) $payout->amount, $decimalDigits, '.', '');
        $amount = $symbolAtRight ? $price . $currencySymbol : $currencySymbol . $price;

        $paidAt = $payout->paidDate ?: $payout->created_at;
        $dateHtml = '—';
        if ($paidAt) {
            try {
                $dt = \Carbon\Carbon::parse($paidAt);
                $dateHtml = $dt->toFormattedDateString() . '<br> ' . $dt->format('g:i:s A');
            } catch (\Throwable $e) {
                $dateHtml = (string) $paidAt;
            }
        }

        $status = $payout->paymentStatus ?? '';
        if ($status === 'Pending' || $status === 'In Process') {
            $statusBadge = '<span class="order_placed badge badge-info"><span>' . e($status) . '</span></span>';
        } elseif ($status === 'Reject' || $status === 'Failed') {
            $statusBadge = '<span class="order_rejected badge badge-danger"><span>' . e($status) . '</span></span>';
        } elseif ($status === 'Success') {
            $statusBadge = '<span class="order_completed badge badge-success"><span>' . e($status) . '</span></span>';
        } else {
            $statusBadge = e($status);
        }

        $withdrawMethod = $payout->withdrawMethod
            ? ($payout->withdrawMethod === 'bank' ? 'Bank Transfer' : ucfirst($payout->withdrawMethod))
            : '—';

        $row = [
            '<input type="checkbox" id="is_open_' . e($payout->id) . '" class="is_open" dataId="' . e($payout->id) . '"><label class="col-3 control-label" for="is_open_' . e($payout->id) . '"></label>',
        ];

        if ($hasVendorColumn) {
            $storeUrl = route('stores.view', $payout->vendorID);
            $storeTitle = $payout->vendor_title ?: '—';
            $row[] = '<a href="' . e($storeUrl) . '" class="redirecttopage">' . e($storeTitle) . '</a>';
        }

        $row[] = e($amount);
        $row[] = e($payout->note ?? '');
        $row[] = $dateHtml;
        $row[] = $statusBadge;
        $row[] = '<span style="text-transform:capitalize">' . e($withdrawMethod) . '</span>';

        $actions = '<span class="action-btn">';
        if ($status !== 'Reject' && $status !== 'Success') {
            $actions .= '<a id="' . e($payout->id) . '" name="vendor_view" data-auth="' . e($payout->vendorID) . '" data-amount="' . e($amount) . '" href="javascript:void(0)" data-toggle="modal" data-target="#bankdetailsModal" class="btn mb-2" title="Manual Pay"><i class="mdi mdi-bank"></i></a>';
        }
        if ($payout->withdrawMethod && $payout->withdrawMethod !== 'bank' && $status !== 'Reject' && $status !== 'Success') {
            $actions .= '<a id="' . e($payout->id) . '" name="vendor_pay" data-auth="' . e($payout->vendorID) . '" data-amount="' . e($price) . '" data-method="' . e($payout->withdrawMethod) . '" href="javascript:void(0)" class="btn mb-2 direct-click-btn" title="Pay Online"><i class="mdi mdi-credit-card"></i></a>';
        }
        if ($status !== 'Reject' && $status !== 'Success') {
            $actions .= '<a id="' . e($payout->id) . '" name="vendor_reject_request" data-toggle="modal" data-target="#cancelRequestModal" data-auth="' . e($payout->vendorID) . '" data-amount="' . e($amount) . '" data-price="' . e($price) . '" href="javascript:void(0)" class="btn mb-2" title="Cancel Request"><i class="mdi mdi-close-circle"></i></a>';
        }
        if ($status === 'In Process') {
            $actions .= '<a id="' . e($payout->id) . '" name="vendor_check_status" data-auth="' . e($payout->vendorID) . '" data-amount="' . e($price) . '" data-method="' . e($payout->withdrawMethod) . '" href="javascript:void(0)" title="Check Payment Status"><i class="mdi mdi-comment-question-outline"></i></a>';
        }
        $actions .= '<a id="' . e($payout->id) . '" class="delete-btn btn-delete-payout" href="javascript:void(0)" title="' . e(trans('lang.delete')) . '"><i class="mdi mdi-delete"></i></a>';
        $actions .= '</span>';

        $row[] = $actions;

        return $row;
    }

    public function datatableDriverDisbursement(Request $request): JsonResponse
    {
        try {
            $driverId = $request->input('driver_id');
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = trim($request->input('search.value', ''));
            $orderCol = (int) $request->input('order.0.column', 4);
            $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
            $hasDriverColumn = ! $driverId;

            $query = DB::table('driver_payouts')
                ->leftJoin('app_users', 'driver_payouts.driverID', '=', 'app_users.id')
                ->select(
                    'driver_payouts.*',
                    'app_users.firstName',
                    'app_users.lastName',
                    'app_users.isOwner'
                )
                ->where(function ($q) {
                    $q->whereNull('app_users.isOwner')->orWhere('app_users.isOwner', false);
                });

            if ($driverId) {
                $query->where('driver_payouts.driverID', $driverId);
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('app_users.firstName', 'like', "%{$search}%")
                        ->orWhere('app_users.lastName', 'like', "%{$search}%")
                        ->orWhere('driver_payouts.amount', 'like', "%{$search}%")
                        ->orWhere('driver_payouts.note', 'like', "%{$search}%")
                        ->orWhere('driver_payouts.paymentStatus', 'like', "%{$search}%")
                        ->orWhere('driver_payouts.withdrawMethod', 'like', "%{$search}%");
                });
            }

            $total = (clone $query)->count();

            $columns = $hasDriverColumn
                ? ['', 'app_users.firstName', 'driver_payouts.amount', 'driver_payouts.note', 'driver_payouts.paidDate', 'driver_payouts.paymentStatus', 'driver_payouts.withdrawMethod', '']
                : ['', 'driver_payouts.amount', 'driver_payouts.note', 'driver_payouts.paidDate', 'driver_payouts.paymentStatus', 'driver_payouts.withdrawMethod', ''];
            $orderBy = $columns[$orderCol] ?? 'driver_payouts.paidDate';
            if ($orderBy === '') {
                $orderBy = 'driver_payouts.paidDate';
            }

            $payouts = $query
                ->orderBy($orderBy, $orderDir)
                ->skip($start)
                ->take($length > 0 ? $length : 10)
                ->get();

            $currency = DB::table('currencies')->where('isActive', 1)->first();
            $currencySymbol = $currency->symbol ?? '₱';
            $symbolAtRight = (bool) ($currency->symbolAtRight ?? false);
            $decimalDigits = (int) ($currency->decimal_degits ?? 2);

            $rows = [];
            foreach ($payouts as $payout) {
                $rows[] = $this->buildDriverDisbursementRow($payout, $hasDriverColumn, $currencySymbol, $symbolAtRight, $decimalDigits);
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            Log::error('PayoutRequestController@datatableDriverDisbursement', ['error' => $e->getMessage()]);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroyDriverPayout(Request $request): JsonResponse
    {
        try {
            $ids = array_values(array_filter(array_unique(array_merge(
                (array) $request->input('ids', []),
                $request->filled('id') ? [$request->input('id')] : []
            ))));

            if ($ids !== []) {
                DB::table('driver_payouts')->whereIn('id', $ids)->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('PayoutRequestController@destroyDriverPayout', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    protected function buildDriverDisbursementRow(object $payout, bool $hasDriverColumn, string $currencySymbol, bool $symbolAtRight, int $decimalDigits): array
    {
        $price = number_format((float) $payout->amount, $decimalDigits, '.', '');
        $amount = $symbolAtRight ? $price . $currencySymbol : $currencySymbol . $price;

        $paidAt = $payout->paidDate ?: $payout->created_at;
        $dateHtml = '—';
        if ($paidAt) {
            try {
                $dt = \Carbon\Carbon::parse($paidAt);
                $dateHtml = $dt->toFormattedDateString() . ' ' . $dt->format('g:i:s A');
            } catch (\Throwable $e) {
                $dateHtml = (string) $paidAt;
            }
        }

        $status = $payout->paymentStatus ?? '';
        if ($status === 'Pending' || $status === 'In Process') {
            $statusBadge = '<span class="order_placed badge badge-info"><span>' . e($status) . '</span></span>';
        } elseif ($status === 'Reject' || $status === 'Failed') {
            $statusBadge = '<span class="order_rejected badge badge-danger"><span>' . e($status) . '</span></span>';
        } elseif ($status === 'Success') {
            $statusBadge = '<span class="order_completed badge badge-success"><span>' . e($status) . '</span></span>';
        } else {
            $statusBadge = e($status);
        }

        $withdrawMethod = $payout->withdrawMethod
            ? ($payout->withdrawMethod === 'bank' ? 'Bank Transfer' : ucfirst($payout->withdrawMethod))
            : '—';

        $row = [
            '<input type="checkbox" id="is_open_' . e($payout->id) . '" class="is_open" dataId="' . e($payout->id) . '"><label class="col-3 control-label" for="is_open_' . e($payout->id) . '"></label>',
        ];

        if ($hasDriverColumn) {
            $driverUrl = route('drivers.view', $payout->driverID);
            $driverName = trim(($payout->firstName ?? '') . ' ' . ($payout->lastName ?? '')) ?: '—';
            $row[] = '<a href="' . e($driverUrl) . '" class="redirecttopage">' . e($driverName) . '</a>';
        }

        $row[] = e($amount);
        $row[] = e($payout->note ?? '');
        $row[] = $dateHtml;
        $row[] = $statusBadge;
        $row[] = '<span style="text-transform:capitalize">' . e($withdrawMethod) . '</span>';

        $actions = '<span class="action-btn">';
        if ($status !== 'Reject' && $status !== 'Success') {
            $actions .= '<a id="' . e($payout->id) . '" name="driver_view" data-auth="' . e($payout->driverID) . '" data-amount="' . e($amount) . '" href="javascript:void(0)" data-toggle="modal" data-target="#bankdetailsModal" title="Manual Pay"><i class="mdi mdi-bank"></i></a>';
        }
        if ($payout->withdrawMethod && $payout->withdrawMethod !== 'bank' && $status !== 'Reject' && $status !== 'Success') {
            $actions .= '<a id="' . e($payout->id) . '" name="driver_pay" data-auth="' . e($payout->driverID) . '" data-amount="' . e($price) . '" data-method="' . e($payout->withdrawMethod) . '" href="javascript:void(0)" title="Pay Online"><i class="mdi mdi-credit-card"></i></a>';
        }
        if ($status !== 'Reject' && $status !== 'Success') {
            $actions .= '<a id="' . e($payout->id) . '" name="driver_reject_request" data-toggle="modal" data-target="#cancelRequestModal" data-auth="' . e($payout->driverID) . '" data-amount="' . e($amount) . '" data-price="' . e($price) . '" href="javascript:void(0)" title="Cancel Request"><i class="mdi mdi-close-circle"></i></a>';
        }
        if ($status === 'In Process') {
            $actions .= '<a id="' . e($payout->id) . '" name="driver_check_status" data-auth="' . e($payout->driverID) . '" data-amount="' . e($price) . '" data-method="' . e($payout->withdrawMethod) . '" href="javascript:void(0)" title="Check Payment Status"><i class="mdi mdi-comment-question-outline"></i></a>';
        }
        $actions .= '<a id="' . e($payout->id) . '" class="delete-btn btn-delete-driver-payout" href="javascript:void(0)" title="' . e(trans('lang.delete')) . '"><i class="mdi mdi-delete"></i></a>';
        $actions .= '</span>';

        $row[] = $actions;

        return $row;
    }
}
