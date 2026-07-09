<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

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

    public function SubscriptionPlanHistory($id = '')
    {
        return view('subscription_plans.history')->with('id', $id);
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
}
