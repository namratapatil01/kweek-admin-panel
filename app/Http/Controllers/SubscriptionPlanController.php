<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionHistory;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("subscription_plans.index");
    }

    public function save($id = '')
    {
        return view("subscription_plans.save")->with('id', $id);
    }

    public function SubscriptionPlanHistory(Request $request, $id = '')
    {
        $storeId = $request->query('storeID', '');
        $providerId = $request->query('providerID', '');
        
        $vendor = null;
        $vendorTitle = '';
        $dineInActive = false;
        $author = '';

        if ($storeId) {
            $vendor = \App\Models\Vendor::find($storeId);
            if ($vendor) {
                $vendorTitle = $vendor->title;
                $dineInActive = (bool) $vendor->dine_in_active;
                $payload = is_array($vendor->payload) ? $vendor->payload : json_decode($vendor->payload ?? '{}', true);
                $author = $payload['author'] ?? '';
                if ($vendor->section_id) {
                    $section = \App\Models\Section::find($vendor->section_id);
                    if ($section && $section->dine_in_active) {
                        $dineInActive = true;
                    }
                }
            }
        }

        return view('subscription_plans.history', [
            'id' => $id ?: $author ?: $providerId ?: '',
            'storeID' => $storeId,
            'providerID' => $providerId,
            'vendorTitle' => $vendorTitle,
            'dineInActive' => $dineInActive,
        ]);
    }

    public function currentSubscriberList($id)
    {
        return view("subscription_plans.current_subscriber", compact('id'));
    }

    public function datatable(Request $request)
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value', '');
            $orderCol = (int) $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'asc');
            $sectionId = $request->cookie('section_id') ?: $request->input('section_id');

            $allPlans = SubscriptionPlan::all();

            $filteredPlans = $allPlans->filter(function($plan) use ($sectionId, $search) {
                $payload = is_array($plan->payload) ? $plan->payload : json_decode($plan->payload ?? '{}', true);
                if (!is_array($payload)) $payload = [];
                
                $isCommission = $payload['isCommissionPlan'] ?? false;
                
                if ($isCommission === true || $isCommission === 'true') {
                    return false;
                }

                if ($sectionId && $plan->sectionId != $sectionId) {
                    return false;
                }

                if ($search) {
                    $searchLower = strtolower($search);
                    $nameMatch = str_contains(strtolower($plan->name ?? ''), $searchLower) || 
                                 str_contains(strtolower($payload['name'] ?? ''), $searchLower);
                    $priceMatch = str_contains(strtolower($plan->price ?? ''), $searchLower) ||
                                  str_contains(strtolower($payload['price'] ?? ''), $searchLower);
                    $expiryMatch = str_contains(strtolower($payload['expiryDay'] ?? ''), $searchLower);

                    if (!$nameMatch && !$priceMatch && !$expiryMatch) {
                        return false;
                    }
                }

                return true;
            });

            $recordsTotal = $filteredPlans->count();

            $sortedPlans = $filteredPlans->sort(function($a, $b) use ($orderCol, $orderDir) {
                $payloadA = is_array($a->payload) ? $a->payload : json_decode($a->payload ?? '{}', true);
                if (!is_array($payloadA)) $payloadA = [];
                $payloadB = is_array($b->payload) ? $b->payload : json_decode($b->payload ?? '{}', true);
                if (!is_array($payloadB)) $payloadB = [];
                
                if ($orderCol == 1 || $orderCol == 0) { // Name
                    $valA = $a->name ?? $payloadA['name'] ?? '';
                    $valB = $b->name ?? $payloadB['name'] ?? '';
                } else if ($orderCol == 2 || $orderCol == 1) { // Price
                    $valA = $a->price ?? $payloadA['price'] ?? 0;
                    $valB = $b->price ?? $payloadB['price'] ?? 0;
                } else {
                    $valA = $a->createdAt ?? '';
                    $valB = $b->createdAt ?? '';
                }

                if ($valA == $valB) return 0;
                $cmp = ($valA < $valB) ? -1 : 1;
                return $orderDir === 'asc' ? $cmp : -$cmp;
            });

            $pagedPlans = $sortedPlans->slice($start, $length > 0 ? $length : 15);

            $data = [];
            foreach ($pagedPlans as $item) {
                $payload = is_array($item->payload) ? $item->payload : json_decode($item->payload ?? '{}', true);
                if (!is_array($payload)) $payload = [];
                $data[] = array_merge($payload, $item->toArray());
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data' => array_values($data),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function storeModel(Request $request)
    {
        try {
            $data = $request->all();
            
            $id = $data['id'] ?? (string) Str::uuid();
            $record = SubscriptionPlan::firstOrNew(['id' => $id]);
            
            if (!$record->exists) {
                $record->id = $id;
                $record->createdAt = now();
            }
            
            $record->name = $data['name'] ?? null;
            $record->price = $data['price'] ?? 0;
            $record->isEnable = filter_var($data['isEnable'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $record->sectionId = $data['sectionId'] ?? null;

            // Merge payload
            $payload = is_array($record->payload) ? $record->payload : json_decode($record->payload ?? '{}', true);
            foreach ($data as $key => $value) {
                $payload[$key] = $value;
            }
            
            $record->payload = $payload;
            $record->save();

            return response()->json(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyModel(Request $request)
    {
        try {
            if ($request->has('id')) {
                SubscriptionPlan::where('id', $request->input('id'))->delete();
            }
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function bulkDestroyModel(Request $request)
    {
        try {
            if ($request->has('ids') && is_array($request->input('ids'))) {
                SubscriptionPlan::whereIn('id', $request->input('ids'))->delete();
            }
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getPlan($id)
    {
        try {
            $plan = SubscriptionPlan::find($id);
            if (!$plan) {
                return response()->json(['success' => false, 'error' => 'Not found'], 404);
            }
            $data = array_merge($plan->toArray(), is_array($plan->payload) ? $plan->payload : json_decode($plan->payload ?? '{}', true));
            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function overview(Request $request) {
        $sectionId = $request->cookie('section_id') ?: $request->input('section_id');
        $allPlans = SubscriptionPlan::all();
        $items = $allPlans->filter(function($plan) use ($sectionId) {
            $payload = is_array($plan->payload) ? $plan->payload : json_decode($plan->payload ?? '{}', true);
            if (!is_array($payload)) $payload = [];
            $isCommission = $payload['isCommissionPlan'] ?? false;
            if ($isCommission === true || $isCommission === 'true') {
                return false;
            }
            if ($sectionId && $plan->sectionId != $sectionId) {
                return false;
            }
            return true;
        });

        $data = [];
        foreach ($items as $item) {
            $payload = is_array($item->payload) ? $item->payload : json_decode($item->payload ?? '{}', true);
            if (!is_array($payload)) $payload = [];
            $data[] = array_merge($payload, $item->toArray());
        }
        return response()->json(['success' => true, 'data' => array_values($data)]);
    }

    public function historyDatatable(Request $request, $id = '')
    {
        try {
            $draw = intval($request->input('draw', 1));
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));
            $search = $request->input('search.value', '');
            $orderCol = intval($request->input('order.0.column', 0));
            $orderDir = $request->input('order.0.dir', 'desc');

            $sectionId = $request->cookie('section_id') ?: $request->input('section_id');

            $query = SubscriptionHistory::query()
                ->select('subscription_histories.*', 'app_users.firstName', 'app_users.lastName')
                ->leftJoin('app_users', 'app_users.id', '=', 'subscription_histories.user_id')
                ->leftJoin('vendors', 'vendors.id', '=', 'app_users.vendorID');

            if ($id) {
                $query->where('subscription_histories.user_id', $id);
            }

            if ($sectionId) {
                $query->where(function ($q) use ($sectionId) {
                    $q->where('app_users.sectionId', $sectionId)
                      ->orWhere('app_users.section_id', $sectionId)
                      ->orWhere('vendors.section_id', $sectionId)
                      ->orWhere('subscription_histories.payload->subscription_plan->sectionId', $sectionId)
                      ->orWhere('subscription_histories.payload->subscription_plan->section_id', $sectionId);
                });
            }

            $totalQuery = clone $query;
            $totalRecords = $totalQuery->count();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('app_users.firstName', 'LIKE', "%{$search}%")
                      ->orWhere('app_users.lastName', 'LIKE', "%{$search}%")
                      ->orWhere('subscription_histories.name', 'LIKE', "%{$search}%")
                      ->orWhere('subscription_histories.payload->subscription_plan->name', 'LIKE', "%{$search}%")
                      ->orWhere('subscription_histories.payload->subscription_plan->type', 'LIKE', "%{$search}%");
                });
            }

            $totalFiltered = $query->count();

            // Order columns mapping
            if ($id == '') {
                $orderColumns = [
                    0 => 'subscription_histories.created_at',
                    1 => 'app_users.firstName',
                    2 => 'subscription_histories.created_at', // Fallback for name/type
                    3 => 'subscription_histories.created_at',
                    4 => 'subscription_histories.created_at',
                    5 => 'subscription_histories.created_at',
                ];
            } else {
                $orderColumns = [
                    0 => 'subscription_histories.created_at',
                    1 => 'subscription_histories.created_at',
                    2 => 'subscription_histories.created_at',
                    3 => 'subscription_histories.created_at',
                    4 => 'subscription_histories.created_at',
                ];
            }

            $orderBy = $orderColumns[$orderCol] ?? 'subscription_histories.created_at';
            $query->orderBy($orderBy, $orderDir === 'asc' ? 'asc' : 'desc');

            $histories = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($histories as $history) {
                $row = [];
                $histId = $history->id;
                
                // checkbox
                $row[] = '<input type="checkbox" id="is_open_' . $histId . '" class="is_open" dataId="' . $histId . '" style="margin: 0;">';
                
                $payload = is_array($history->payload) ? $history->payload : json_decode($history->payload ?? '{}', true);
                if (!is_array($payload)) $payload = [];
                
                $subPlan = [];
                if (isset($payload['subscription_plan'])) {
                    if (is_array($payload['subscription_plan'])) {
                        $subPlan = $payload['subscription_plan'];
                    } elseif (is_string($payload['subscription_plan'])) {
                        $subPlan = json_decode($payload['subscription_plan'], true) ?: [];
                    }
                }

                $planName = $subPlan['name'] ?? ($history->name ?: '');
                $planType = $subPlan['type'] ?? '';
                
                // vendor name link
                if ($id == '') {
                    $vendorName = trim(($history->firstName ?? '') . ' ' . ($history->lastName ?? ''));
                    if (!$vendorName) {
                        $vendorName = 'Unknown Vendor';
                    }
                    $vendorId = $history->user_id ?? $payload['user_id'] ?? '';
                    $editRoute = route('vendors.edit', $vendorId);
                    $row[] = '<a href="' . $editRoute . '" class="redirecttopage" >' . e($vendorName) . '</a>';
                }

                // plan name link
                $planId = $subPlan['id'] ?? '';
                $planSaveRoute = route('subscription-plans.save', $planId);
                $row[] = '<a href="' . $planSaveRoute . '" class="redirecttopage" >' . e($planName) . '</a>';

                // plan type badge
                if (strtolower($planType) === 'free') {
                    $row[] = '<span class="badge badge-success">' . strtoupper(e($planType)) . '</span>';
                } elseif ($planType) {
                    $row[] = '<span class="badge badge-danger">' . strtoupper(e($planType)) . '</span>';
                } else {
                    $row[] = '<span class="badge">-</span>';
                }

                // plan expires at
                $expiryDate = $payload['expiry_date'] ?? null;
                if ($expiryDate && $expiryDate !== '-1') {
                    try {
                        $dt = \Carbon\Carbon::parse($expiryDate);
                        $row[] = '<span class="dt-time">' . $dt->format('D M d Y g:i:s A') . '</span>';
                    } catch (\Exception $e) {
                        $row[] = e($expiryDate);
                    }
                } else {
                    $row[] = trans('lang.unlimited');
                }

                // purchase date
                $createdAt = $payload['data_created_at'] ?? $payload['createdAt'] ?? $history->created_at;
                if ($createdAt && $createdAt !== '-1') {
                    try {
                        $dt = \Carbon\Carbon::parse($createdAt);
                        $row[] = '<span class="dt-time">' . $dt->format('D M d Y g:i:s A') . '</span>';
                    } catch (\Exception $e) {
                        $row[] = e($createdAt);
                    }
                } else {
                    $row[] = trans('lang.unlimited');
                }

                $data[] = $row;
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered ?? $totalRecords,
                'data' => $data,
            ]);

        } catch (\Throwable $e) {
            Log::error('SubscriptionPlanController@historyDatatable: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyHistory(Request $request)
    {
        try {
            $ids = $request->input('ids');
            if (is_array($ids)) {
                SubscriptionHistory::whereIn('id', $ids)->delete();
            } elseif ($request->has('id')) {
                SubscriptionHistory::where('id', $request->input('id'))->delete();
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
