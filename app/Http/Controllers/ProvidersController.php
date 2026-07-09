<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\ProviderOrder;
use App\Support\PayloadMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProvidersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('providers.index');
    }

    public function create()
    {
        return view('providers.create');
    }

    public function edit($id)
    {
        return view('providers.edit')->with('id', $id);
    }

    public function view($id)
    {
        return view('providers.view')->with('id', $id);
    }

    public function datatable(Request $request)
    {
        try {
            $sectionId = $request->input('section_id', '');
            $status = $request->input('status', '');
            $fromDate = $request->input('from_date', '');
            $toDate = $request->input('to_date', '');

            $draw = intval($request->input('draw', 1));
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));
            $search = $request->input('search.value', '');
            $orderCol = intval($request->input('order.0.column', 5));
            $orderDir = $request->input('order.0.dir', 'desc');

            $query = AppUser::providers();

            if ($sectionId) {
                $query->where(function ($q) use ($sectionId) {
                    $q->where('sectionId', $sectionId)
                        ->orWhere('section_id', $sectionId)
                        ->orWhereNull('sectionId')
                        ->orWhere('sectionId', '')
                        ->orWhereNull('section_id')
                        ->orWhere('section_id', '');
                });
            }

            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }

            if ($fromDate) {
                $query->where(function ($q) use ($fromDate) {
                    $q->whereDate('created_at', '>=', $fromDate)
                        ->orWhereDate('createdAt', '>=', $fromDate);
                });
            }
            if ($toDate) {
                $query->where(function ($q) use ($toDate) {
                    $q->whereDate('created_at', '<=', $toDate)
                        ->orWhereDate('createdAt', '<=', $toDate);
                });
            }

            $totalRecords = (clone $query)->count();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('firstName', 'LIKE', "%{$search}%")
                        ->orWhere('lastName', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phoneNumber', 'LIKE', "%{$search}%");
                });
            }

            $totalFiltered = $query->count();

            $orderColumns = [
                0 => 'created_at',
                1 => 'firstName',
                2 => 'email',
                3 => 'created_at',
                4 => 'created_at',
                5 => 'created_at',
                6 => 'created_at',
                7 => 'active',
                8 => 'created_at',
            ];
            $orderBy = $orderColumns[$orderCol] ?? 'created_at';
            $query->orderBy($orderBy, $orderDir === 'asc' ? 'asc' : 'desc');

            $providers = $query->skip($start)->take($length)->get();
            $placeholderImage = $this->getPlaceholderImage();

            $userIds = $providers->pluck('id')->all();
            $orderCounts = [];
            if (!empty($userIds)) {
                $orderCounts = ProviderOrder::query()
                    ->whereIn('authorID', $userIds)
                    ->selectRaw('authorID, COUNT(*) as total')
                    ->groupBy('authorID')
                    ->pluck('total', 'authorID')
                    ->toArray();
            }

            $data = [];
            foreach ($providers as $provider) {
                $data[] = $this->buildRow($provider, $placeholderImage, $orderCounts);
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('ProvidersController@datatable: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildRow(AppUser $provider, string $placeholderImage, array $orderCounts): array
    {
        $row = [];
        $id = $provider->id;
        $payload = is_array($provider->payload) ? $provider->payload : [];

        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];
        $checkDelete = in_array('providers.delete', $permissions);

        if ($checkDelete) {
            $row[] = '<input type="checkbox" id="is_open_' . $id . '" class="is_open" dataId="' . $id . '"><label class="col-3 control-label" for="is_open_' . $id . '"></label>';
        }

        $editUrl = route('providers.edit', $id);
        $viewUrl = route('providers.view', $id);
        $photo = $provider->profilePictureURL ?: $placeholderImage;
        $name = e(trim(($provider->firstName ?? '') . ' ' . ($provider->lastName ?? '')));
        $row[] = '<img class="rounded" style="width:50px" src="' . e($photo) . '" alt="image" onerror="this.onerror=null;this.src=\'' . e($placeholderImage) . '\'">'
            . '<a href="' . $viewUrl . '" class="redirecttopage left_space">' . $name . '</a>';

        $row[] = $this->shortEmail($provider->email ?? '');

        $subscriptionPlan = $payload['subscription_plan'] ?? null;
        $planName = '';
        if (is_array($subscriptionPlan) && !empty($subscriptionPlan['name'])) {
            $planName = $subscriptionPlan['name'];
        } elseif (is_string($subscriptionPlan)) {
            $planName = $subscriptionPlan;
        }
        $row[] = e($planName);

        $expiryRaw = $payload['subscriptionExpiryDate'] ?? null;
        $expiryDisplay = ($planName && ($expiryRaw === null || $expiryRaw === ''))
            ? trans('lang.unlimited')
            : $this->formatExpiry($expiryRaw);
        $row[] = $expiryDisplay;

        $created = $provider->createdAt ?: $provider->created_at;
        if ($created) {
            $dt = \Carbon\Carbon::parse($created);
            $row[] = '<span class="wrap-word">' . $dt->format('D, M d Y') . '<br>' . $dt->format('h:i:s A') . '</span>';
        } else {
            $row[] = '';
        }

        $row[] = (string) ($orderCounts[$id] ?? 0);

        $activeChecked = $provider->active ? 'checked' : '';
        $row[] = '<label class="switch"><input type="checkbox" ' . $activeChecked . ' id="' . $id . '" name="isActive"><span class="slider round"></span></label>';

        $walletUrl = url('/walletstransaction/providerID=' . $id);
        $actions = '<span class="action-btn">';
        if ($planName) {
            $planRoute = route('subscription.subscriptionPlanHistory', $id);
            $actions .= '<a href="' . $planRoute . '" data-toggle="tooltip" title="' . e(trans('lang.subscription_plans')) . '"><i class="mdi mdi-crown"></i></a>';
        }
        $actions .= '<a href="' . $walletUrl . '" data-toggle="tooltip" title="Wallet Transaction"><i class="mdi mdi-wallet"></i></a>';
        $actions .= '<a href="' . $viewUrl . '" data-toggle="tooltip" title="' . e(trans('lang.view')) . '"><i class="mdi mdi-eye"></i></a>';
        $actions .= '<a href="' . $editUrl . '" data-toggle="tooltip" title="' . e(trans('lang.edit')) . '"><i class="mdi mdi-lead-pencil"></i></a>';
        if ($checkDelete) {
            $actions .= '<a id="' . $id . '" class="delete-btn" name="user-delete" href="javascript:void(0)" data-toggle="tooltip" title="' . e(trans('lang.delete')) . '"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';
        $row[] = $actions;

        return $row;
    }

    public function storeProvider(Request $request)
    {
        try {
            $id = $request->input('id');
            if (empty($id)) {
                return response()->json(['error' => 'User ID is required'], 422);
            }

            if (AppUser::where('email', $request->input('email'))->where('role', 'provider')->exists()) {
                return response()->json(['error' => 'This email is already registered.'], 422);
            }

            $data = $request->only([
                'id', 'firstName', 'lastName', 'email', 'phoneNumber',
                'active', 'profilePictureURL', 'userBankDetails',
                'adminCommission', 'subscription_plan', 'subscriptionPlanId', 'subscriptionExpiryDate',
            ]);

            $data['role'] = 'provider';
            $data['active'] = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $data['isActive'] = $data['active'];
            $data['sectionId'] = $request->input('sectionId', $request->input('section_id'));
            $data['section_id'] = $data['sectionId'];
            $data['wallet_amount'] = 0;
            $data['createdAt'] = now();

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->input('password'));
            }

            if (isset($data['userBankDetails']) && is_array($data['userBankDetails'])) {
                $data['userBankDetails'] = json_encode($data['userBankDetails']);
            }

            if ($request->has('location')) {
                $loc = $request->input('location');
                $data['latitude'] = $loc['latitude'] ?? null;
                $data['longitude'] = $loc['longitude'] ?? null;
            }

            $prototype = new AppUser();
            $fillable = array_diff(Schema::getColumnListing($prototype->getTable()), ['created_at', 'updated_at']);
            $mapped = PayloadMapper::map($data, $fillable, ['payload', 'userBankDetails']);
            $attributes = $mapped['attributes'];

            if (!empty($mapped['overflow'])) {
                $existing = is_array($attributes['payload'] ?? null) ? $attributes['payload'] : [];
                $attributes['payload'] = array_merge($existing, $mapped['overflow']);
            }

            $provider = AppUser::updateOrCreate(['id' => $id], $attributes);

            if ($request->filled('subscription_plan')) {
                $this->addSubscriptionHistory($id, $request->all());
            }

            return response()->json(['success' => true, 'data' => $provider]);
        } catch (\Exception $e) {
            Log::error('ProvidersController@storeProvider error: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus(Request $request)
    {
        $id = $request->input('id');
        $value = filter_var($request->input('value'), FILTER_VALIDATE_BOOLEAN);

        AppUser::providers()->where('id', $id)->update([
            'active' => $value ? 1 : 0,
            'isActive' => $value ? 1 : 0,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $this->deleteProviderUser($id);

        return response()->json(['success' => true]);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['error' => 'No items provided'], 422);
        }

        foreach ($ids as $id) {
            $this->deleteProviderUser($id);
        }

        return response()->json(['success' => true]);
    }

    private function deleteProviderUser(?string $userId): void
    {
        if (!$userId) {
            return;
        }

        if (Schema::hasTable('wallet')) {
            DB::table('wallet')->where('user_id', $userId)->delete();
        }
        if (Schema::hasTable('favorite_provider')) {
            DB::table('favorite_provider')->where('provider_id', $userId)->delete();
        }
        AppUser::where('id', $userId)->where('role', 'provider')->delete();
    }

    public function getMeta()
    {
        try {
            $placeholder = $this->getPlaceholderImage();
            $countryCodeRow = DB::table('settings')->where('key', 'defaultCountryCode')->first()
                ?? DB::table('settings')->where('id', 'globalSettings')->first();
            $defaultCountryCode = '';
            if ($countryCodeRow) {
                $val = json_decode($countryCodeRow->value ?? '', true);
                $defaultCountryCode = is_array($val)
                    ? ($val['defaultCountryCode'] ?? '')
                    : ($countryCodeRow->value ?? '');
            }

            $vendorSettings = DB::table('settings')->where('id', 'vendor')->value('value');
            $subscriptionModel = false;
            if ($vendorSettings) {
                $decoded = json_decode($vendorSettings, true);
                $subscriptionModel = (bool) ($decoded['subscription_model'] ?? false);
            }

            return response()->json([
                'placeholderImage' => $placeholder,
                'defaultCountryCode' => $defaultCountryCode,
                'subscription_model' => $subscriptionModel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'placeholderImage' => '',
                'defaultCountryCode' => '',
                'subscription_model' => false,
            ]);
        }
    }

    public function getSubscriptionPlans(Request $request)
    {
        try {
            $sectionId = $request->input('section_id', '');
            $query = DB::table('subscription_plans')->where('isEnable', 1);
            if ($sectionId) {
                $query->where('sectionId', $sectionId);
            }
            $plans = $query->get(['id', 'name', 'expiryDay', 'price', 'sectionId']);

            return response()->json(['plans' => $plans]);
        } catch (\Exception $e) {
            return response()->json(['plans' => []]);
        }
    }

    public function getSubscriptionPlan($id)
    {
        try {
            $plan = DB::table('subscription_plans')->where('id', $id)->first();
            if (!$plan) {
                return response()->json(['error' => 'Plan not found'], 404);
            }

            $expiryDate = null;
            if ($plan->expiryDay && $plan->expiryDay != '-1') {
                $expiryDate = now()->addDays((int) $plan->expiryDay)->toIso8601String();
            }

            return response()->json([
                'data' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'expiryDay' => $plan->expiryDay,
                    'expiryDate' => $expiryDate,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function addSubscriptionHistory(string $userId, array $requestData): void
    {
        $plan = $requestData['subscription_plan'] ?? null;
        if (!$plan) {
            return;
        }

        DB::table('subscription_histories')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'name' => is_array($plan) ? ($plan['name'] ?? 'Subscription') : 'Subscription',
            'createdAt' => now(),
            'payload' => json_encode([
                'expiry_date' => $requestData['subscriptionExpiryDate'] ?? null,
                'subscription_plan' => $plan,
                'payment_type' => 'cod',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getPlaceholderImage(): string
    {
        $raw = DB::table('settings')->where('id', 'placeHolderImage')->value('value')
            ?? DB::table('settings')->where('key', 'placeHolderImage')->value('value');

        if (!$raw) {
            return '';
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? ($decoded['image'] ?? '') : (string) $raw;
    }

    private function shortEmail(?string $email): string
    {
        if (!$email) {
            return '';
        }
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return e($email);
        }
        $local = $parts[0];
        if (strlen($local) <= 3) {
            return e($email);
        }

        return e(substr($local, 0, 3) . '***@' . $parts[1]);
    }

    private function formatExpiry(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $parsed = PayloadMapper::parseTimestamp($value);
        if ($parsed) {
            $dt = \Carbon\Carbon::parse($parsed);

            return $dt->format('D, M d Y') . ' ' . $dt->format('h:i:s A');
        }

        if (is_string($value) && $value !== '') {
            try {
                return \Carbon\Carbon::parse($value)->format('D, M d Y h:i:s A');
            } catch (\Exception $e) {
                return e($value);
            }
        }

        return trans('lang.unlimited');
    }
}
