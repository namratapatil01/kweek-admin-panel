<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        return view('OnDemandService.workers.index')->with('id', $id);
    }

    public function WorkersCreate($id = '')
    {
        return view('OnDemandService.workers.create')->with('id', $id);
    }

    public function WorkersEdit($id)
    {
        return view('OnDemandService.workers.edit')->with('id', $id);
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
                    ->orWhere('section_id', $sectionId);
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
