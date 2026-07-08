<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller
{
    
    public function __construct()
    {
       $this->middleware('auth');
    }

    public function index($id='')
    {
        $user = null;
        $vendor = null;
        $parsedId = $id;

        if (str_starts_with($id, 'storeID=')) {
            $parsedId = str_replace('storeID=', '', $id);
            $vendor = \App\Models\Vendor::find($parsedId);
            if ($vendor) {
                // Still need the user object for UI details if it exists
                $user = \App\Models\AppUser::where('vendorID', $vendor->id)->where('role', 'vendor')->first();
                if (!$user && isset($vendor->payload['author'])) {
                    $user = \App\Models\AppUser::find($vendor->payload['author']);
                }
                
                // The wallet table stores the AppUser's ID (vendor author) in the user_id column
                $parsedId = $user ? $user->id : $vendor->id; 
            }
        } elseif (str_starts_with($id, 'driverID=')) {
            $parsedId = str_replace('driverID=', '', $id);
            $user = \App\Models\AppUser::find($parsedId);
        } elseif (!empty($id)) {
            $user = \App\Models\AppUser::find($id);
        }

        return view("transactions.index")->with([
            'id' => $id,
            'parsedId' => $parsedId,
            'user' => $user,
            'vendor' => $vendor
        ]);
    }

    public function datatable(Request $request)
    {
        try {
            $start  = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));
            $search = $request->input('search.value');
            
            $userId = $request->input('user_id');

            $orderCol = intval($request->input('order.0.column', 4));
            $orderDir = $request->input('order.0.dir', 'desc');

            $query = \App\Models\Wallet::query();

            if (!empty($userId)) {
                $query->where('user_id', $userId);
            }

            // Total count for this specific query (e.g. for this user)
            $totalRecords = (clone $query)->count();
            $totalFiltered = $totalRecords;

            // Search
            if ($search && strlen($search) >= 1) {
                $query->where(function ($q) use ($search) {
                    $q->where('amount', 'LIKE', "%{$search}%")
                      ->orWhere('note', 'LIKE', "%{$search}%")
                      ->orWhere('payment_method', 'LIKE', "%{$search}%")
                      ->orWhere('payment_status', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('firstName', 'LIKE', "%{$search}%")
                             ->orWhere('lastName', 'LIKE', "%{$search}%")
                             ->orWhere('role', 'LIKE', "%{$search}%");
                      });
                });
                $totalFiltered = $query->count();
            }

            // Order Columns map
            if (empty($userId)) {
                $columns = ['', 'name', 'role', 'amount', 'date', 'note', 'payment_method', 'payment_status', ''];
            } else {
                $columns = ['', 'amount', 'date', 'note', 'payment_method', 'payment_status', ''];
            }
            
            $orderByField = $columns[$orderCol] ?? 'date';

            if ($orderByField === 'name' || $orderByField === 'role') {
                // sort by relationship (approximate or just fallback)
                $query->orderBy('date', $orderDir);
            } else {
                if (!empty($orderByField)) {
                    $query->orderBy($orderByField, $orderDir);
                } else {
                    $query->orderBy('date', 'desc');
                }
            }

            $transactions = $query->with('user')->skip($start)->take($length)->get();

            // Fetch Currency
            $currencyRow = \DB::table('currencies')->where('isActive', 1)->first();
            $currencySymbol = $currencyRow ? $currencyRow->symbol : '$';
            $currencyAtRight = $currencyRow ? (bool)$currencyRow->symbolAtRight : false;
            $decimalDigits = $currencyRow ? $currencyRow->decimal_degits : 2;

            $data = [];
            foreach ($transactions as $tx) {
                $row = [];
                $id = $tx->id;

                // 1. Checkbox
                $row[] = '<input type="checkbox" id="is_open_' . $id . '" class="is_open" dataId="' . $id . '"><label class="col-3 control-label" for="is_open_' . $id . '" ></label>';

                // User and Role
                if (empty($userId)) {
                    if ($tx->user) {
                        $role = $tx->user->role;
                        $routeuser = "Javascript:void(0)";
                        if ($role == "customer") {
                            $routeuser = route('users.view', $tx->user_id);
                        } else if ($role == "driver") {
                            $routeuser = route('drivers.view', $tx->user_id);
                        } else if ($role == "vendor") {
                            if ($tx->user->vendorID != '') {
                                $routeuser = route('stores.view', $tx->user->vendorID);
                            }
                        }
                        $name = trim($tx->user->firstName . ' ' . $tx->user->lastName);
                        $row[] = '<a href="' . $routeuser . '">' . $name . '</a>';
                        $row[] = $role;
                    } else {
                        $row[] = 'Unknown User';
                        $row[] = '';
                    }
                }

                // Amount
                $amountVal = number_format((float)$tx->amount, $decimalDigits);
                $amountStr = $currencyAtRight ? $amountVal . $currencySymbol : $currencySymbol . $amountVal;
                
                if ($tx->isTopUp || $tx->payment_method == "Cancelled Order Payment") {
                    $row[] = '<span class="text-green"><i class="mdi mdi-arrow-up-bold status-icon"></i> +' . $amountStr . '</span>';
                } else {
                    $row[] = '<span class="text-red"><i class="mdi mdi-arrow-down-bold status-icon"></i> (-' . $amountStr . ')</span>';
                }

                // Date
                $dateHtml = '';
                if ($tx->date) {
                    $dt = \Carbon\Carbon::parse($tx->date);
                    $dateHtml = $dt->format('D M d Y') . '<br>' . $dt->format('h:i:s A');
                }
                $row[] = $dateHtml;

                // Note
                $row[] = $tx->note ?? '';

                // Payment Method
                $payment_method_html = '-';
                if ($tx->payment_method && $tx->payment_method != 'tax') {
                    $img = '';
                    $pm = strtolower($tx->payment_method);
                    if (str_contains($pm, 'stripe')) $img = 'stripe.png';
                    elseif (str_contains($pm, 'razorpay')) $img = 'razorepay.png';
                    elseif (str_contains($pm, 'paypal')) $img = 'paypal.png';
                    elseif (str_contains($pm, 'payfast')) $img = 'payfast.png';
                    elseif (str_contains($pm, 'paystack')) $img = 'paystack.png';
                    elseif (str_contains($pm, 'flutterwave')) $img = 'flutter_wave.png';
                    elseif (str_contains($pm, 'mercado pago')) $img = 'marcado_pago.png';
                    elseif (str_contains($pm, 'wallet')) $img = 'emart_wallet.png';
                    elseif (str_contains($pm, 'paytm')) $img = 'paytm.png';
                    elseif (str_contains($pm, 'xendit')) $img = 'xendit.png';
                    elseif (str_contains($pm, 'orangepay') || str_contains($pm, 'maya') || str_contains($pm, 'instapay')) $img = 'orangeMoney.png';
                    elseif (str_contains($pm, 'midtrans')) $img = 'midtrans.png';
                    elseif ($tx->payment_method == 'Cancelled Order Payment') $img = 'cancel_order.png';
                    elseif ($tx->payment_method == 'Refund Amount') $img = 'refund_amount.png';
                    elseif ($tx->payment_method == 'Referral Amount') $img = 'reffral_amount.png';
                    
                    if ($img) {
                        $payment_method_html = '<img alt="image" style="max-width:100px;" src="' . asset('images/payment/' . $img) . '">';
                    } else {
                        $payment_method_html = $tx->payment_method;
                    }
                }
                $row[] = $payment_method_html;

                // Payment Status
                if ($tx->payment_status == 'success') {
                    $row[] = '<span class="badge badge-success">' . ucfirst($tx->payment_status) . '</span>';
                } elseif (strtolower($tx->payment_status) == 'refund success') {
                    $row[] = '<span class="badge badge-danger">' . ucfirst($tx->payment_status) . '</span>';
                } elseif ($tx->payment_status == 'undefined') {
                    $row[] = '<span class="badge badge-warning">' . ucfirst($tx->payment_status) . '</span>';
                } else {
                    $row[] = '<span class="badge badge-secondary">' . ucfirst($tx->payment_status) . '</span>';
                }

                // Actions
                $row[] = '<span class="action-btn"><a id="' . $id . '" class="delete-btn" name="transaction-delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a></span>';

                $data[] = $row;
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
                'filteredData' => $data, // for frontend compatibility
            ]);
        } catch (\Exception $e) {
            \Log::error('TransactionController@datatable error: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function ownerWalletTranscation($id='')
    {
        return view("owners.wallet_transaction")->with('id',$id);
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->input('id');
            \App\Models\Wallet::where('id', $id)->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('TransactionController@destroy error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids');
            if (is_array($ids)) {
                \App\Models\Wallet::whereIn('id', $ids)->delete();
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('TransactionController@bulkDestroy error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
