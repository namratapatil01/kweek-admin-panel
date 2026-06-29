<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
    public function vendorDisbursements($id="")
    {
        return view("payoutRequests.vendor.disbursement_index")->with("id",$id);
    }
    public function driverDisbursements($id="")
    {
        return view("payoutRequests.drivers.disbursement_index")->with("id",$id);
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
}
