<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\ProviderWorker;
use App\Services\Worker\WorkerAuthService;
use App\Support\CatalogEntityWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OnDemandServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function Category()
    {
        return view('OnDemandService.categories.index');
    }

    public function CategoryCreate()
    {
        return view('OnDemandService.categories.create');
    }

    public function CategoryEdit($id)
    {
        return view('OnDemandService.categories.edit')->with('id', $id);
    }

    public function Coupons($id = '')
    {
        return view('OnDemandService.coupons.index')->with('id', $id);
    }

    public function CouponCreate($id = '')
    {
        $providers = $this->getProviders();

        return view('OnDemandService.coupons.create', [
            'id' => $id,
            'providers' => $providers,
            'providerId' => request('id', $id),
        ]);
    }

    public function CouponEdit($id)
    {
        $coupon = DB::table('providers_coupons')->where('id', $id)->first();
        if (! $coupon) {
            abort(404);
        }

        $payload = json_decode($coupon->payload ?? '{}', true) ?? [];
        $providers = $this->getProviders();

        return view('OnDemandService.coupons.edit', [
            'id' => $id,
            'coupon' => $coupon,
            'payload' => $payload,
            'providers' => $providers,
        ]);
    }

    public function couponsDatatable(Request $request): JsonResponse
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim($request->input('search.value', ''));
        $providerId = $request->input('provider_id', '');
        $sectionId = $request->input('section_id', '');

        $query = DB::table('providers_coupons');

        if ($providerId !== '') {
            $query->where('providerId', $providerId);
        }

        if ($sectionId !== '') {
            $query->where(function ($q) use ($sectionId) {
                $q->where('sectionId', $sectionId)
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.sectionId')) = ?", [$sectionId]);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.code')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.discount')) LIKE ?", ["%{$search}%"])
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();
        $coupons = $query->orderByDesc('created_at')->skip($start)->take($length > 0 ? $length : 10)->get();

        $providerIds = $coupons->pluck('providerId')->filter()->unique()->values()->toArray();
        $providerNames = DB::table('app_users')
            ->whereIn('id', $providerIds)
            ->get(['id', 'firstName', 'lastName'])
            ->mapWithKeys(fn ($u) => [$u->id => trim(($u->firstName ?? '') . ' ' . ($u->lastName ?? ''))]);

        $rows = [];
        foreach ($coupons as $coupon) {
            $payload = json_decode($coupon->payload ?? '{}', true) ?? [];
            $code = $payload['code'] ?? $coupon->title ?? $coupon->name ?? '—';
            $discount = $payload['discount'] ?? $coupon->price ?? '0';
            $discountType = $payload['discountType'] ?? $payload['discount_type'] ?? 'Percentage';
            $isPublic = $payload['isPublic'] ?? $payload['is_public'] ?? false;
            $expiresAt = $payload['expiresAt'] ?? $payload['expires_at'] ?? null;
            $providerName = $providerNames[$coupon->providerId] ?? '';

            $discountLabel = $discountType === 'Percentage'
                ? $discount . '%'
                : number_format((float) $discount, 2);

            $privacyBadge = $isPublic
                ? '<span class="badge badge-success py-2 px-3">Public</span>'
                : '<span class="badge badge-danger py-2 px-3">Private</span>';

            $expiryLabel = '—';
            if ($expiresAt) {
                try {
                    $expiryLabel = date('D M d Y', strtotime($expiresAt)) . '<br>' . date('g:i:s A', strtotime($expiresAt));
                } catch (\Throwable $e) {
                    $expiryLabel = (string) $expiresAt;
                }
            }

            $enabled = (bool) ($coupon->isEnabled ?? $coupon->isEnable ?? false);
            $toggleChecked = $enabled ? 'checked' : '';
            $toggleHtml = '<label class="switch"><input type="checkbox" class="toggle-provider-coupon" data-id="' . e($coupon->id) . '" ' . $toggleChecked . '><span class="slider round"></span></label>';

            $editUrl = route('ondemand.coupons.edit', $coupon->id) . ($providerId ? ('?id=' . urlencode($providerId)) : '');
            $actions = '<span class="action-btn">'
                . '<a href="' . $editUrl . '" data-toggle="tooltip" title="Edit"><i class="mdi mdi-lead-pencil"></i></a>'
                . '<a href="javascript:void(0)" class="delete-btn btn-delete-provider-coupon" data-id="' . e($coupon->id) . '" data-toggle="tooltip" title="Delete"><i class="mdi mdi-delete"></i></a>'
                . '</span>';

            $row = [
                '<input type="checkbox" class="is_open" dataId="' . e($coupon->id) . '">',
                '<a href="' . $editUrl . '">' . e($code) . '</a>',
                $discountLabel,
            ];

            if ($providerId === '') {
                $providerLink = $coupon->providerId
                    ? '<a href="' . route('providers.view', $coupon->providerId) . '">' . e($providerName ?: 'Unknown') . '</a>'
                    : '—';
                $row[] = $providerLink;
            }

            $row[] = $privacyBadge;
            $row[] = $expiryLabel;
            $row[] = $toggleHtml;
            $row[] = $actions;

            $rows[] = $row;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rows,
        ]);
    }

    public function couponStore(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'discount' => 'required|numeric|min:0',
            'discountType' => 'required|in:Percentage,Fix Price',
            'providerId' => 'required|string',
            'expiresAt' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'sectionId' => 'nullable|string',
        ]);

        $id = (string) Str::uuid();
        $expiresAt = $request->expiresAt
            ? date('Y-m-d H:i:s', strtotime($request->expiresAt))
            : null;

        $payload = [
            'code' => strtoupper($request->code),
            'discount' => $request->discount,
            'discountType' => $request->discountType,
            'description' => $request->description ?? '',
            'expiresAt' => $expiresAt,
            'isPublic' => $request->boolean('isPublic'),
            'image' => $request->image ?: '',
            'providerId' => $request->providerId,
            'sectionId' => $request->sectionId ?: null,
        ];

        DB::table('providers_coupons')->insert([
            'id' => $id,
            'providerId' => $request->providerId,
            'sectionId' => $request->sectionId ?: null,
            'isEnabled' => $request->boolean('isEnabled') ? 1 : 0,
            'isEnable' => $request->boolean('isEnabled') ? 1 : 0,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function couponUpdate(Request $request, string $id): JsonResponse
    {
        $existing = DB::table('providers_coupons')->where('id', $id)->first();
        if (! $existing) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $request->validate([
            'code' => 'required|string|max:50',
            'discount' => 'required|numeric|min:0',
            'discountType' => 'required|in:Percentage,Fix Price',
            'providerId' => 'required|string',
            'expiresAt' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'sectionId' => 'nullable|string',
        ]);

        $oldPayload = json_decode($existing->payload ?? '{}', true) ?? [];
        $expiresAt = $request->expiresAt
            ? date('Y-m-d H:i:s', strtotime($request->expiresAt))
            : null;

        $payload = array_merge($oldPayload, [
            'code' => strtoupper($request->code),
            'discount' => $request->discount,
            'discountType' => $request->discountType,
            'description' => $request->description ?? '',
            'expiresAt' => $expiresAt,
            'isPublic' => $request->boolean('isPublic'),
            'providerId' => $request->providerId,
            'sectionId' => $request->sectionId ?: ($oldPayload['sectionId'] ?? null),
        ]);

        if ($request->filled('image')) {
            $payload['image'] = $request->image;
        }

        DB::table('providers_coupons')->where('id', $id)->update([
            'providerId' => $request->providerId,
            'sectionId' => $request->sectionId ?: $existing->sectionId,
            'isEnabled' => $request->boolean('isEnabled') ? 1 : 0,
            'isEnable' => $request->boolean('isEnabled') ? 1 : 0,
            'payload' => json_encode($payload),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function couponToggle(string $id): JsonResponse
    {
        $coupon = DB::table('providers_coupons')->where('id', $id)->first();
        if (! $coupon) {
            return response()->json(['success' => false], 404);
        }

        $newStatus = $coupon->isEnabled ? 0 : 1;
        DB::table('providers_coupons')->where('id', $id)->update([
            'isEnabled' => $newStatus,
            'isEnable' => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'enabled' => (bool) $newStatus]);
    }

    public function couponDestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if ($request->filled('id')) {
            $ids[] = $request->input('id');
        }
        $ids = array_values(array_filter(array_unique($ids)));
        if ($ids !== []) {
            DB::table('providers_coupons')->whereIn('id', $ids)->delete();
        }

        return response()->json(['success' => true]);
    }

    public function providersList(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getProviders($request->input('section_id')),
        ]);
    }

    public function Services($id = '')
    {
        return view('OnDemandService.services.index')->with('id', $id);
    }

    public function ServicesCreate($id = '')
    {
        return view('OnDemandService.services.create')->with('id', $id);
    }

    public function ServicesEdit($id)
    {
        return view('OnDemandService.services.edit')->with('id', $id);
    }

    public function Bookings($id = '')
    {
        return view('OnDemandService.bookings.index')->with('id', $id);
    }

    public function BookingsCreate($id = '')
    {
        return view('OnDemandService.bookings.create')->with('id', $id);
    }

    public function BookingsEdit($id = '', $pid = '', $aid = '', $rid = '')
    {
        return view('OnDemandService.bookings.edit')->with('id', $id)->with('pid', $pid)->with('aid', $aid)->with('rid', $rid);
    }

    public function BookingsPrint($id)
    {
        return view('OnDemandService.bookings.print')->with('id', $id);
    }

    public function Workers($id = '')
    {
        $providerId = (string) ($id ?? '');
        $query = ProviderWorker::query();
        if ($providerId !== '') {
            $query->where('providerId', $providerId);
        }
        $query->orderByRaw('COALESCE(createdAt, created_at) desc');

        $workers = $query->get();
        $placeholderImage = $this->getPlaceholderImage();
        $currency = $this->getActiveCurrency();

        $providerIds = $workers->pluck('providerId')->filter()->unique()->values()->all();
        $providers = collect();
        if (! empty($providerIds)) {
            $providers = AppUser::query()
                ->whereIn('id', $providerIds)
                ->get(['id', 'firstName', 'lastName'])
                ->keyBy('id');
        }

        $permissions = session('user_permissions', []);
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?: [];
        }
        if (! is_array($permissions)) {
            $permissions = [];
        }
        $checkDelete = in_array('ondemand.workers.delete', $permissions, true);

        $workerRows = [];
        foreach ($workers as $worker) {
            $workerRows[] = $this->buildWorkerRow(
                $worker,
                $providers,
                $placeholderImage,
                $currency,
                $checkDelete,
                $providerId
            );
        }

        return view('OnDemandService.workers.index', [
            'id' => $providerId,
            'workerRows' => $workerRows,
            'workersCount' => count($workerRows),
            'checkDeletePermission' => $checkDelete,
        ]);
    }

    public function WorkersCreate($id = '')
    {
        return view('OnDemandService.workers.create')->with('id', $id);
    }

    public function WorkersEdit($id)
    {
        return view('OnDemandService.workers.edit')->with('id', $id);
    }

    public function workersDatatable(Request $request): JsonResponse
    {
        try {
            $providerId = $request->input('provider_id', '');
            $status = $request->input('status', '');
            $fromDate = $request->input('from_date', '');
            $toDate = $request->input('to_date', '');

            $draw = intval($request->input('draw', 1));
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));
            $search = trim($request->input('search.value', ''));
            $orderCol = intval($request->input('order.0.column', 1));
            $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

            $query = ProviderWorker::query();

            if ($providerId !== '') {
                $query->where('providerId', $providerId);
            }

            if ($status === 'active') {
                $query->where(function ($q) {
                    $q->where('isActive', 1)
                        ->orWhere('payload->active', true)
                        ->orWhere('payload->active', 1);
                });
            } elseif ($status === 'inactive') {
                $query->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->whereNull('isActive')->orWhere('isActive', 0);
                    })->where(function ($inner) {
                        $inner->whereNull('payload->active')
                            ->orWhere('payload->active', false)
                            ->orWhere('payload->active', 0);
                    });
                });
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

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('payload->firstName', 'LIKE', "%{$search}%")
                        ->orWhere('payload->lastName', 'LIKE', "%{$search}%")
                        ->orWhere('payload->email', 'LIKE', "%{$search}%")
                        ->orWhere('payload->phoneNumber', 'LIKE', "%{$search}%")
                        ->orWhere('name', 'LIKE', "%{$search}%")
                        ->orWhere('title', 'LIKE', "%{$search}%");
                });
            }

            $totalFiltered = $query->count();
            $query->orderByRaw('COALESCE(createdAt, created_at) ' . $orderDir);

            $workers = $query->skip($start)->take(max($length, 1))->get();
            $placeholderImage = $this->getPlaceholderImage();
            $currency = $this->getActiveCurrency();

            $providerIds = $workers->pluck('providerId')->filter()->unique()->values()->all();
            $providers = collect();
            if (!empty($providerIds)) {
                $providers = AppUser::query()
                    ->whereIn('id', $providerIds)
                    ->get(['id', 'firstName', 'lastName'])
                    ->keyBy('id');
            }

            $permissions = session('user_permissions', []);
            if (is_string($permissions)) {
                $permissions = json_decode($permissions, true) ?: [];
            }
            if (!is_array($permissions)) {
                $permissions = [];
            }
            $checkDelete = in_array('ondemand.workers.delete', $permissions, true);

            $data = [];
            foreach ($workers as $worker) {
                $data[] = $this->buildWorkerRow($worker, $providers, $placeholderImage, $currency, $checkDelete, (string) $providerId);
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('OnDemandServiceController@workersDatatable: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function workersToggleStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $value = filter_var($request->input('value'), FILTER_VALIDATE_BOOLEAN);

        $worker = ProviderWorker::query()->find($id);
        if (!$worker) {
            return response()->json(['error' => 'Worker not found'], 404);
        }

        $worker->isActive = $value ? 1 : 0;
        $worker->mergePayload(['active' => $value]);
        $worker->save();

        return response()->json(['success' => true]);
    }

    public function workersDestroy(Request $request): JsonResponse
    {
        $id = $request->input('id');
        ProviderWorker::query()->where('id', $id)->delete();
        AppUser::query()->where('id', $id)->where('role', 'worker')->delete();

        return response()->json(['success' => true]);
    }

    public function workersBulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['error' => 'No items provided'], 422);
        }

        ProviderWorker::query()->whereIn('id', $ids)->delete();
        AppUser::query()->whereIn('id', $ids)->where('role', 'worker')->delete();

        return response()->json(['success' => true]);
    }

    public function workersStore(Request $request): JsonResponse
    {
        try {
            $providerId = $request->input('providerId');
            if (!$providerId) {
                return response()->json(['error' => 'Provider is required'], 422);
            }

            $provider = AppUser::query()->where('id', $providerId)->where('role', 'provider')->first();
            if (!$provider) {
                return response()->json(['error' => 'Provider not found'], 404);
            }

            $email = $request->input('email');
            if (AppUser::query()->where('email', $email)->where('role', 'worker')->exists()
                || ProviderWorker::query()->where('payload->email', $email)->exists()) {
                return response()->json(['error' => 'This email is already registered.'], 422);
            }

            $id = $request->input('id') ?: (string) Str::uuid();
            $password = $request->input('password', Str::random(10));
            $photo = $this->storeWorkerImage($request->input('profilePictureURL'));

            $data = [
                'id' => $id,
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'email' => $email,
                'phoneNumber' => $request->input('phoneNumber'),
                'salary' => $request->input('salary'),
                'address' => $request->input('address'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'profilePictureURL' => $photo,
                'providerId' => $providerId,
                'active' => filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN),
                'online' => false,
                'reviewsCount' => 0,
                'reviewsSum' => 0,
                'password_hash' => Hash::make($password),
                'createdAt' => now(),
            ];

            $worker = CatalogEntityWriter::write(new ProviderWorker(), $data);
            app(WorkerAuthService::class)->syncAppUser($worker, $password);

            return response()->json(['success' => true, 'data' => $worker->toDocumentArray()]);
        } catch (\Exception $e) {
            Log::error('OnDemandServiceController@workersStore: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function workersUpdate(Request $request, string $id): JsonResponse
    {
        try {
            $worker = ProviderWorker::query()->find($id);
            if (!$worker) {
                return response()->json(['error' => 'Worker not found'], 404);
            }

            $providerId = $request->input('providerId', $worker->providerId);
            $photo = $request->input('profilePictureURL');
            if (is_string($photo) && str_starts_with($photo, 'data:image')) {
                $photo = $this->storeWorkerImage($photo);
            } elseif (!$photo) {
                $payload = is_array($worker->payload) ? $worker->payload : [];
                $photo = $payload['profilePictureURL'] ?? null;
            }

            $data = [
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'email' => $request->input('email'),
                'phoneNumber' => $request->input('phoneNumber'),
                'salary' => $request->input('salary'),
                'address' => $request->input('address'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'profilePictureURL' => $photo,
                'providerId' => $providerId,
                'active' => filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN),
            ];

            if ($request->filled('password')) {
                $data['password_hash'] = Hash::make($request->input('password'));
            }

            $worker = CatalogEntityWriter::write(new ProviderWorker(), $data, $worker);
            app(WorkerAuthService::class)->syncAppUser(
                $worker,
                $request->filled('password') ? $request->input('password') : null
            );

            return response()->json(['success' => true, 'data' => $worker->toDocumentArray()]);
        } catch (\Exception $e) {
            Log::error('OnDemandServiceController@workersUpdate: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function storeWorkerImage(?string $imageData): ?string
    {
        if (!$imageData) {
            return null;
        }

        if (!str_starts_with($imageData, 'data:image')) {
            return $this->normalizeImageUrl($imageData) ?: $imageData;
        }

        if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            return null;
        }

        $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode(substr($imageData, strpos($imageData, ',') + 1), true);
        if ($binary === false) {
            return null;
        }

        $filename = (string) Str::uuid() . '.' . $extension;
        $relative = 'images/' . $filename;
        $absolute = public_path('storage/' . $relative);
        if (!is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0755, true);
        }
        file_put_contents($absolute, $binary);

        return '/storage/' . $relative;
    }

    private function buildWorkerRow(
        ProviderWorker $worker,
        $providers,
        string $placeholderImage,
        array $currency,
        bool $checkDelete,
        string $filterProviderId
    ): array {
        $id = $worker->id;
        $payload = is_array($worker->payload) ? $worker->payload : [];

        $firstName = $payload['firstName'] ?? $worker->firstName ?? $worker->name ?? '';
        $lastName = $payload['lastName'] ?? $worker->lastName ?? '';
        $email = $payload['email'] ?? $worker->email ?? '';
        $salary = $payload['salary'] ?? $worker->salary ?? $worker->price ?? 0;
        $online = (bool) ($payload['online'] ?? $worker->online ?? false);
        $active = array_key_exists('active', $payload)
            ? (bool) $payload['active']
            : (bool) ($worker->isActive ?? false);
        $photo = $payload['profilePictureURL'] ?? $worker->profilePictureURL ?? '';
        $photo = $this->normalizeImageUrl($photo);
        if ($photo === '' || $photo === null) {
            $photo = $placeholderImage;
        }
        $fallbackPhoto = e($placeholderImage);

        $editUrl = route('ondemand.workers.edit', $id);
        if ($filterProviderId !== '') {
            $editUrl .= '?id=' . urlencode($filterProviderId);
        }

        $name = e(trim($firstName . ' ' . $lastName));
        if ($name === '') {
            $name = e($worker->title ?: ($email ?: 'Worker'));
        }

        $info = '<img class="rounded" style="width:50px;height:50px;object-fit:cover" src="' . e($photo) . '" alt="image" onerror="this.onerror=null;this.src=\'' . $fallbackPhoto . '\'">'
            . '<a class="left_space" href="' . $editUrl . '">' . $name . '</a>';

        $decimals = (int) ($currency['decimal_degits'] ?? 2);
        $symbol = $currency['symbol'] ?? '';
        $amount = number_format((float) $salary, $decimals, '.', '');
        $salaryDisplay = !empty($currency['symbolAtRight'])
            ? e($amount . $symbol)
            : e($symbol . $amount);

        $providerKey = $worker->providerId ?? ($payload['providerId'] ?? '');
        $provider = $providerKey && isset($providers[$providerKey]) ? $providers[$providerKey] : null;
        $providerName = $provider
            ? trim(($provider->firstName ?? '') . ' ' . ($provider->lastName ?? ''))
            : '';
        if ($providerName === '') {
            $providerHtml = e(trans('lang.unknown'));
        } else {
            $providerView = route('providers.view', $providerKey);
            $providerHtml = '<a href="' . $providerView . '">' . e($providerName) . '</a>';
        }

        $activeChecked = $active ? 'checked' : '';
        $statusHtml = '<label class="switch"><input type="checkbox" ' . $activeChecked . ' id="' . e($id) . '" name="isActive"><span class="slider round"></span></label>';

        $actions = '<span class="action-btn"><a href="' . $editUrl . '" data-toggle="tooltip" title="' . e(trans('lang.edit')) . '"><i class="mdi mdi-lead-pencil"></i></a>';
        if ($checkDelete) {
            $actions .= '<a id="' . e($id) . '" class="delete-btn" name="worker-delete" href="javascript:void(0)" data-toggle="tooltip" title="' . e(trans('lang.delete')) . '"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';

        $row = [
            'checkbox' => $checkDelete
                ? '<input type="checkbox" id="is_open_' . e($id) . '" class="is_open" dataId="' . e($id) . '"><label class="col-3 control-label" for="is_open_' . e($id) . '"></label>'
                : '',
            'info' => $info,
            'email' => $this->shortEmail($email),
            'salary' => $salaryDisplay,
            'provider' => $providerHtml,
            'online' => $online ? 'Online' : 'Offline',
            'status' => $statusHtml,
            'actions' => $actions,
        ];

        return $row;
    }

    private function getPlaceholderImage(): string
    {
        $raw = DB::table('settings')->where('id', 'placeHolderImage')->value('value')
            ?? DB::table('settings')->where('key', 'placeHolderImage')->value('value');

        if (!$raw) {
            return asset('images/default_user.png');
        }

        $decoded = json_decode($raw, true);
        $image = is_array($decoded) ? ($decoded['image'] ?? '') : (string) $raw;

        return $this->normalizeImageUrl($image) ?: asset('images/default_user.png');
    }

    private function getActiveCurrency(): array
    {
        $currency = DB::table('currencies')->where('isActive', 1)->first();

        return [
            'symbol' => $currency->symbol ?? '$',
            'symbolAtRight' => (bool) ($currency->symbolAtRight ?? false),
            'decimal_degits' => $currency->decimal_degits ?? 2,
        ];
    }

    private function normalizeImageUrl(?string $url): string
    {
        if (!$url) {
            return '';
        }

        // Keep local storage paths relative so they work on any host/port.
        if (preg_match('#(/storage/.+)$#i', $url, $matches)) {
            return $matches[1];
        }

        if (str_starts_with($url, 'storage/')) {
            return '/' . ltrim($url, '/');
        }

        return $url;
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

    protected function getProviders(?string $sectionId = null)
    {
        $query = DB::table('app_users')
            ->where('role', 'provider')
            ->where(function ($q) {
                $q->where('active', 1)->orWhere('isActive', 1);
            })
            ->orderBy('firstName');

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

        return $query->get(['id', 'firstName', 'lastName', 'email', 'sectionId', 'section_id'])
            ->map(function ($p) {
                $name = trim(($p->firstName ?? '') . ' ' . ($p->lastName ?? ''));
                if ($name === '') {
                    $name = $p->email ?: 'Unknown Provider';
                }

                return [
                    'id' => $p->id,
                    'name' => $name,
                    'email' => $p->email,
                    'sectionId' => $p->sectionId ?? $p->section_id,
                ];
            })
            ->values();
    }
}
