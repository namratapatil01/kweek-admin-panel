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
        return view("transactions.index")->with('id',$id);
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

            // Total count before filtering
            $totalFiltered = $query->count();

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

            $data = [];
            foreach ($transactions as $tx) {
                $row = $tx->toArray();
                // Add user info
                if ($tx->user) {
                    $row['Name'] = trim($tx->user->firstName . ' ' . $tx->user->lastName);
                    $row['role'] = $tx->user->role;
                    $row['vendorID'] = $tx->user->vendorID;
                } else {
                    $row['Name'] = 'Unknown User';
                    $row['role'] = '';
                    $row['vendorID'] = '';
                }
                
                // Format amount with currency symbol logic to be handled on frontend
                $row['date'] = $tx->date ? $tx->date->toIso8601String() : '';
                $data[] = $row;
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => \App\Models\Wallet::count(),
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
