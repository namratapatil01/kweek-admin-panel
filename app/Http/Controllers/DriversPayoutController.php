<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AppUser;

class DriversPayoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id = '')
    {
        return view("drivers_payouts.index")->with('id', $id);
    }

    public function create($id = '')
    {
        return view("drivers_payouts.create")->with('id', $id);
    }

    /**
     * AJAX server-side processing for driver payouts DataTable.
     */
    public function datatable(Request $request)
    {
        try {
            $driverId = $request->input('driver_id', '');

            // DataTables parameters
            $draw    = intval($request->input('draw', 1));
            $start   = intval($request->input('start', 0));
            $length  = intval($request->input('length', 10));
            $search  = $request->input('search.value', '');
            $orderCol = intval($request->input('order.0.column', 3));
            $orderDir = $request->input('order.0.dir', 'desc');

            $query = DB::table('driver_payouts')
                ->leftJoin('app_users', 'driver_payouts.driverID', '=', 'app_users.id')
                ->select(
                    'driver_payouts.*',
                    'app_users.firstName',
                    'app_users.lastName',
                    'app_users.serviceType'
                )
                ->where('driver_payouts.paymentStatus', 'Success');

            if ($driverId) {
                $query->where('driver_payouts.driverID', $driverId);
            }

            // Total count
            $totalQuery = DB::table('driver_payouts')->where('paymentStatus', 'Success');
            if ($driverId) {
                $totalQuery->where('driverID', $driverId);
            }
            $totalCount = $totalQuery->count();

            // Search
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('driver_payouts.amount', 'LIKE', "%{$search}%")
                      ->orWhere('driver_payouts.note', 'LIKE', "%{$search}%")
                      ->orWhere('app_users.firstName', 'LIKE', "%{$search}%")
                      ->orWhere('app_users.lastName', 'LIKE', "%{$search}%");
                });
            }

            $totalFiltered = $query->count();

            // Columns mapping for ordering
            if ($driverId) {
                $columns = ['', 'driver_payouts.amount', 'driver_payouts.created_at', 'driver_payouts.note'];
            } else {
                $columns = ['', 'app_users.firstName', 'driver_payouts.amount', 'driver_payouts.created_at', 'driver_payouts.note'];
            }

            $orderByField = $columns[$orderCol] ?? 'driver_payouts.created_at';
            $query->orderBy($orderByField ?: 'driver_payouts.created_at', $orderDir);

            // Paginate
            $payouts = $query->skip($start)->take($length)->get();

            // Fetch active currency settings
            $currency = DB::table('currencies')->where('isActive', 1)->first();
            $currencySymbol = $currency->symbol ?? '$';
            $symbolAtRight = (bool)($currency->symbolAtRight ?? false);
            $decimal_digits = $currency->decimal_degits ?? 2;

            $data = [];
            foreach ($payouts as $payout) {
                $row = [];
                $pid = $payout->id;

                // Checkbox
                $row[] = '<input type="checkbox" id="is_open_' . $pid . '" class="is_open" dataId="' . $pid . '"><label class="col-3 control-label" for="is_open_' . $pid . '"></label>';

                // Driver Name (if no driverId)
                if (!$driverId) {
                    $driverUrl = route('drivers.view', $payout->driverID);
                    $driverName = e($payout->firstName . ' ' . $payout->lastName);
                    $row[] = '<a href="' . $driverUrl . '">' . $driverName . '</a>';
                }

                // Amount
                $formattedAmount = number_format($payout->amount, $decimal_digits);
                if ($symbolAtRight) {
                    $row[] = $formattedAmount . $currencySymbol;
                } else {
                    $row[] = $currencySymbol . $formattedAmount;
                }

                // Paid Date
                if ($payout->created_at) {
                    $dt = \Carbon\Carbon::parse($payout->created_at);
                    $row[] = $dt->format('D M d Y') . ' ' . $dt->format('h:i:s A');
                } else {
                    $row[] = '';
                }

                // Note
                $row[] = e($payout->note);

                // Actions
                $row[] = '<span class="action-btn"><a id="' . $pid . '" class="delete-btn" name="driver_payouts-delete" href="javascript:void(0)" data-toggle="tooltip" title="' . trans('lang.delete') . '"><i class="mdi mdi-delete"></i></a></span>';

                $data[] = $row;
            }

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $totalCount,
                'recordsFiltered' => $totalFiltered,
                'data'            => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('DriversPayoutController@datatable error: ' . $e->getMessage());
            return response()->json([
                'draw'            => intval($request->input('draw', 1)),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new driver payout and decrement driver's wallet amount.
     */
    public function store(Request $request)
    {
        try {
            $id = $request->input('id');
            $driverID = $request->input('driverID');
            $amount = floatval($request->input('amount'));
            $note = $request->input('note', '');

            if (empty($driverID) || $amount <= 0) {
                return response()->json(['error' => 'Invalid parameters'], 422);
            }

            $driver = AppUser::find($driverID);
            if (!$driver) {
                return response()->json(['error' => 'Driver not found'], 404);
            }

            if ($driver->wallet_amount < $amount) {
                return response()->json(['error' => 'Insufficient wallet balance'], 400);
            }

            DB::beginTransaction();

            // Decrement wallet balance
            $driver->decrement('wallet_amount', $amount);

            // Create payout record
            $payoutId = $id ?: uniqid();
            DB::table('driver_payouts')->insert([
                'id' => $payoutId,
                'driverID' => $driverID,
                'amount' => $amount,
                'note' => $note,
                'paymentStatus' => 'Success',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'id' => $payoutId]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DriversPayoutController@store error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a single payout record.
     */
    public function destroy(Request $request)
    {
        try {
            $id = $request->input('id');
            if (empty($id)) {
                return response()->json(['error' => 'No ID provided'], 422);
            }

            DB::table('driver_payouts')->where('id', $id)->delete();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('DriversPayoutController@destroy error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete payout records.
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return response()->json(['error' => 'No IDs provided'], 422);
            }

            DB::table('driver_payouts')->whereIn('id', $ids)->delete();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('DriversPayoutController@bulkDestroy error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all drivers (for dropdown list).
     */
    public function getDrivers()
    {
        try {
            $drivers = AppUser::drivers()->orderBy('firstName', 'asc')->get(['id', 'firstName', 'lastName', 'email', 'phoneNumber', 'wallet_amount']);
            return response()->json(['success' => true, 'data' => $drivers]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
