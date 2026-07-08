<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\Vendor;
use App\Support\PayloadMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /* ------------------------------------------------------------------ */
    /*  Pages                                                              */
    /* ------------------------------------------------------------------ */

    public function index(): View
    {
        if (request()->is('vendors/approved')) {
            $type = 'approved';
        } elseif (request()->is('vendors/pending')) {
            $type = 'pending';
        } else {
            $type = 'all';
        }

        return view('vendors.index', compact('type'));
    }

    public function create(): View
    {
        return view('vendors.create');
    }

    public function edit($id): View
    {
        return view('vendors.edit', ['id' => $id]);
    }

    public function DocumentList($id): View
    {
        return view('vendors.document_list', ['id' => $id]);
    }

    public function DocumentUpload($ownerId, $id): View
    {
        return view('vendors.document_upload', compact('ownerId', 'id'));
    }

    /* ------------------------------------------------------------------ */
    /*  DataTable (server-side)                                            */
    /* ------------------------------------------------------------------ */

    public function datatable(Request $request)
    {
        try {
            $type      = $request->input('type', 'all');
            $sectionId = $request->input('section_id', '');
            $status    = $request->input('status', '');
            $fromDate  = $request->input('from_date', '');
            $toDate    = $request->input('to_date', '');

            $draw     = intval($request->input('draw', 1));
            $start    = intval($request->input('start', 0));
            $length   = intval($request->input('length', 10));
            $search   = $request->input('search.value', '');
            $orderCol = intval($request->input('order.0.column', 6));
            $orderDir = $request->input('order.0.dir', 'desc');

            $query = AppUser::vendors();

            if ($type === 'approved') {
                $query->approved();
            } elseif ($type === 'pending') {
                $query->pending();
            }

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
                $query->whereDate('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            $totalQuery = clone $query;
            $totalRecords = $totalQuery->count();

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
                2 => 'firstName',
                3 => 'email',
                4 => 'created_at',
                5 => 'created_at',
                6 => 'created_at',
                7 => 'active',
                8 => 'created_at',
            ];
            $orderBy = $orderColumns[$orderCol] ?? 'created_at';
            $query->orderBy($orderBy, $orderDir === 'asc' ? 'asc' : 'desc');

            $vendors = $query->skip($start)->take($length)->get();

            $placeholderImage = $this->getPlaceholderImage();

            $vendorIds = $vendors->pluck('vendorID')->filter()->unique()->values()->all();
            $stores = [];
            if (!empty($vendorIds)) {
                $stores = Vendor::whereIn('id', $vendorIds)->pluck('title', 'id')->toArray();
            }

            $userIds = $vendors->pluck('id')->all();
            $docVerifications = DB::table('documents_verify')
                ->whereIn('id', $userIds)
                ->pluck('documents', 'id')
                ->toArray();

            $data = [];
            foreach ($vendors as $vendor) {
                $data[] = $this->buildRow($vendor, $stores, $placeholderImage, $docVerifications, $type);
            }

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data'            => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('VendorController@datatable: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'draw'            => intval($request->input('draw', 1)),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => $e->getMessage(),
            ], 500);
        }
    }

    private function buildRow(AppUser $vendor, array $stores, string $placeholderImage, array $docVerifications, string $type): array
    {
        $row = [];
        $id  = $vendor->id;
        $payload = is_array($vendor->payload) ? $vendor->payload : [];

        $editUrl     = route('vendors.edit', $id);
        $documentUrl = route('vendors.document', $id);
        $storeId     = $vendor->vendorID;
        $storeTitle  = $storeId && isset($stores[$storeId]) ? $stores[$storeId] : '';
        $storeViewUrl = $storeId ? route('stores.view', $storeId) : '';

        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];
        $checkDelete = ($type === 'pending' && in_array('pending.vendors.delete', $permissions))
            || ($type === 'approved' && in_array('approve.vendors.delete', $permissions))
            || ($type === 'all' && in_array('vendors.delete', $permissions));

        if ($checkDelete) {
            $row[] = '<input type="checkbox" id="is_open_' . $id . '" class="is_open" dataId="' . $id . '" data-vendorid="' . e($storeId ?? '') . '"><label class="col-3 control-label" for="is_open_' . $id . '"></label>';
        }

        $verified = $this->getDocumentStatusIcon($id, $docVerifications);
        $photo = $vendor->profilePictureURL ?: $placeholderImage;
        $name  = e(trim(($vendor->firstName ?? '') . ' ' . ($vendor->lastName ?? '')));
        $row[] = '<img class="rounded" style="width:50px" src="' . e($photo) . '" alt="image" onerror="this.onerror=null;this.src=\'' . e($placeholderImage) . '\'">'
            . '<a href="' . $editUrl . '" class="redirecttopage left_space">' . $name . '</a>' . $verified;

        if ($storeTitle && $storeViewUrl) {
            $row[] = '<a href="' . $storeViewUrl . '" class="redirecttopage left_space">' . e($storeTitle) . '</a>';
        } else {
            $row[] = '';
        }

        $phone = $vendor->phoneNumber ?? '';
        $maskedPhone = $this->maskPhone($phone);
        $email = $this->shortEmail($vendor->email ?? '');
        $row[] = $email . '<br>' . e($maskedPhone);

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

        if ($vendor->created_at) {
            $dt = \Carbon\Carbon::parse($vendor->created_at);
            $row[] = '<span class="wrap-word">' . $dt->format('D, M d Y') . '<br>' . $dt->format('h:i:s A') . '</span>';
        } else {
            $row[] = '';
        }

        $activeChecked = $vendor->active ? 'checked' : '';
        $row[] = '<label class="switch"><input type="checkbox" ' . $activeChecked . ' id="' . $id . '" name="isActive"><span class="slider round"></span></label>';

        $actions = '<span class="action-btn">';
        $actions .= '<a href="' . $documentUrl . '" data-toggle="tooltip" title="' . e(trans('lang.document')) . '"><i class="fa fa-file"></i></a>';
        if ($planName) {
            $planRoute = route('subscription.subscriptionPlanHistory', $id);
            $actions .= '<a href="' . $planRoute . '" data-toggle="tooltip" title="' . e(trans('lang.subscription_plans')) . '"><i class="mdi mdi-crown"></i></a>';
        }
        $actions .= '<a href="' . $editUrl . '" data-toggle="tooltip" title="' . e(trans('lang.edit')) . '"><i class="mdi mdi-lead-pencil"></i></a>';
        if ($checkDelete) {
            $actions .= '<a id="' . $id . '" data-vendorid="' . e($storeId ?? '') . '" class="delete-btn" name="user-delete" href="javascript:void(0)" data-toggle="tooltip" title="' . e(trans('lang.delete')) . '"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';
        $row[] = $actions;

        return $row;
    }

    private function getDocumentStatusIcon(string $userId, array $docVerifications): string
    {
        if (!isset($docVerifications[$userId])) {
            return '';
        }

        $docs = json_decode($docVerifications[$userId], true);
        if (empty($docs) || !is_array($docs)) {
            return '';
        }

        $approved = 0;
        $rejected = 0;
        $total = count($docs);

        foreach ($docs as $d) {
            $status = $d['status'] ?? '';
            if ($status === 'approved') {
                $approved++;
            }
            if ($status === 'rejected') {
                $rejected++;
            }
        }

        if ($approved === $total && $total > 0) {
            return '<i class="mdi mdi-verified verified-icon" data-toggle="tooltip" title="Verified"></i>';
        }
        if ($rejected > 0) {
            return '<i class="mdi mdi-close-circle unverified-icon" data-toggle="tooltip" title="Rejected" style="color:red;"></i>';
        }

        return '';
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

    private function maskPhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) <= 4) {
            return $phone;
        }

        return str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
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

    /* ------------------------------------------------------------------ */
    /*  Toggle / delete                                                    */
    /* ------------------------------------------------------------------ */

    public function toggleStatus(Request $request)
    {
        $id    = $request->input('id');
        $value = filter_var($request->input('value'), FILTER_VALIDATE_BOOLEAN);

        AppUser::vendors()->where('id', $id)->update(['active' => $value ? 1 : 0]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $id       = $request->input('id');
        $vendorId = $request->input('vendorId');

        $this->deleteVendorUser($id, $vendorId);

        return response()->json(['success' => true]);
    }

    public function bulkDestroy(Request $request)
    {
        $items = $request->input('items', []);
        if (empty($items)) {
            return response()->json(['error' => 'No items provided'], 422);
        }

        foreach ($items as $item) {
            $this->deleteVendorUser($item['id'] ?? '', $item['vendorId'] ?? null);
        }

        return response()->json(['success' => true]);
    }

    private function deleteVendorUser(string $userId, ?string $vendorId): void
    {
        if ($userId) {
            DB::table('wallet')->where('user_id', $userId)->delete();
            DB::table('documents_verify')->where('id', $userId)->delete();
            AppUser::where('id', $userId)->delete();
        }

        if ($vendorId) {
            DB::table('vendor_products')->where('vendorID', $vendorId)->delete();
            DB::table('favorite_vendors')->where('store_id', $vendorId)->delete();
            DB::table('stories')->where('vendorID', $vendorId)->delete();
            Vendor::where('id', $vendorId)->delete();
        }
    }

    /* ------------------------------------------------------------------ */
    /*  CRUD API                                                           */
    /* ------------------------------------------------------------------ */

    public function getVendor($id)
    {
        $vendor = AppUser::vendors()->find($id);
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $data = array_merge($vendor->toArray(), $vendor->payload ?? []);

        return response()->json(['data' => $data]);
    }

    public function storeVendor(Request $request)
    {
        try {
            $id = $request->input('id');
            if (empty($id)) {
                return response()->json(['error' => 'User ID is required'], 422);
            }

            if (AppUser::where('email', $request->input('email'))->exists()) {
                return response()->json(['error' => 'This email is already registered.'], 422);
            }

            $data = $request->only([
                'id', 'firstName', 'lastName', 'email', 'phoneNumber',
                'active', 'profilePictureURL', 'userBankDetails',
                'subscription_plan', 'subscriptionPlanId', 'subscriptionExpiryDate',
            ]);

            $data['role'] = 'vendor';
            $data['active'] = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $data['isDocumentVerify'] = false;
            $data['vendorID'] = $request->input('vendorID');
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

            $prototype = new AppUser();
            $fillable = array_diff(Schema::getColumnListing($prototype->getTable()), ['created_at', 'updated_at']);
            $mapped = PayloadMapper::map($data, $fillable, ['payload', 'userBankDetails']);
            $attributes = $mapped['attributes'];

            if (!empty($mapped['overflow'])) {
                $existing = is_array($attributes['payload'] ?? null) ? $attributes['payload'] : [];
                $attributes['payload'] = array_merge($existing, $mapped['overflow']);
            }

            $vendor = AppUser::updateOrCreate(['id' => $id], $attributes);

            if ($request->filled('subscription_plan')) {
                $this->addSubscriptionHistory($id, $request->all());
            }

            return response()->json(['success' => true, 'data' => $vendor]);
        } catch (\Exception $e) {
            Log::error('VendorController@storeVendor: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateVendor(Request $request, $id)
    {
        try {
            $vendor = AppUser::vendors()->find($id);
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $data = $request->only([
                'firstName', 'lastName', 'active', 'profilePictureURL', 'userBankDetails',
                'subscription_plan', 'subscriptionPlanId', 'subscriptionExpiryDate',
            ]);

            if (isset($data['active'])) {
                $data['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            }

            if (isset($data['userBankDetails']) && is_array($data['userBankDetails'])) {
                $data['userBankDetails'] = json_encode($data['userBankDetails']);
            }

            $prototype = new AppUser();
            $fillable = array_diff(Schema::getColumnListing($prototype->getTable()), ['created_at', 'updated_at']);
            $mapped = PayloadMapper::map($data, $fillable, ['payload', 'userBankDetails']);
            $attributes = $mapped['attributes'];

            if (!empty($mapped['overflow'])) {
                $existing = is_array($vendor->payload) ? $vendor->payload : [];
                $attributes['payload'] = array_merge($existing, $mapped['overflow']);
            }

            if ($request->filled('subscriptionExpiryDate')) {
                $existing = is_array($vendor->payload) ? $vendor->payload : [];
                $existing['subscriptionExpiryDate'] = $request->input('subscriptionExpiryDate');
                $attributes['payload'] = array_merge(
                    is_array($attributes['payload'] ?? null) ? $attributes['payload'] : [],
                    $existing
                );
            }

            $vendor->update($attributes);

            return response()->json(['success' => true, 'data' => $vendor->fresh()]);
        } catch (\Exception $e) {
            Log::error('VendorController@updateVendor: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
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
                'placeholderImage'   => $placeholder,
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
                    'id'         => $plan->id,
                    'name'       => $plan->name,
                    'expiryDay'  => $plan->expiryDay,
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

        $historyId = (string) \Illuminate\Support\Str::uuid();
        DB::table('subscription_histories')->insert([
            'id'         => $historyId,
            'user_id'    => $userId,
            'name'       => is_array($plan) ? ($plan['name'] ?? 'Subscription') : 'Subscription',
            'createdAt'  => now(),
            'payload'    => json_encode([
                'expiry_date'       => $requestData['subscriptionExpiryDate'] ?? null,
                'subscription_plan' => $plan,
                'payment_type'      => 'cod',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
