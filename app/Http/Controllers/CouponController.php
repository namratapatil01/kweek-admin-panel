<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** List page */
    public function index(Request $request)
    {
        // Vendor filter (from URL: coupon/{id})
        $vendorId = $request->route('id');
        $vendors  = DB::table('vendors')->whereNotNull('title')->where('title', '!=', '')->orderBy('title')->get(['id', 'title']);

        return view('coupons.index', compact('vendorId', 'vendors'));
    }

    /** DataTables JSON endpoint */
    public function datatable(Request $request): JsonResponse
    {
        $draw    = (int) $request->input('draw', 1);
        $start   = (int) $request->input('start', 0);
        $length  = (int) $request->input('length', 10);
        $search  = trim($request->input('search.value', ''));
        $vendorId = $request->input('vendor_id', '');

        $query = DB::table('coupons');

        if ($vendorId) {
            $query->where('vendorID', $vendorId);
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

        // Bulk-fetch vendor titles
        $vendorIds    = $coupons->pluck('vendorID')->filter()->unique()->values()->toArray();
        $vendorTitles = DB::table('vendors')->whereIn('id', $vendorIds)->pluck('title', 'id');

        $rows = [];
        foreach ($coupons as $coupon) {
            $payload = json_decode($coupon->payload ?? '{}', true) ?? [];

            // Support both Firebase keys and admin form keys
            $code         = $payload['code'] ?? $coupon->title ?? $coupon->name ?? '—';
            $discount     = $payload['discount'] ?? $coupon->price ?? '0';
            $discountType = $payload['discountType'] ?? $payload['discount_type'] ?? 'Percentage';
            $isPublic     = $payload['isPublic'] ?? $payload['is_public'] ?? 0;
            $expiresAt    = $payload['expiresAt'] ?? $payload['expires_at'] ?? null;

            $discountLabel = $discountType === 'Percentage'
                ? $discount . '%'
                : '₹' . number_format((float) $discount, 2);

            $privacyBadge = $isPublic
                ? '<span class="badge badge-success px-3 py-1">Public</span>'
                : '<span class="badge badge-warning px-3 py-1">Private</span>';

            $expiryLabel = '—';
            if ($expiresAt) {
                try {
                    $expiryLabel = date('D M d Y g:i:s A', strtotime($expiresAt));
                } catch (\Throwable $e) {
                    $expiryLabel = (string) $expiresAt;
                }
            }

            $vendorTitle = $vendorTitles[$coupon->vendorID] ?? '—';
            $storeLink   = $coupon->vendorID
                ? '<a href="' . route('stores.view', $coupon->vendorID) . '" class="text-primary">' . e($vendorTitle) . '</a>'
                : '—';

            $enabled = (bool) ($coupon->isEnabled ?? $coupon->isEnable ?? $coupon->isActive ?? false);
            $toggleChecked = $enabled ? 'checked' : '';
            $toggleHtml = '<label class="coupon-toggle-switch">
                <input type="checkbox" class="toggle-enabled" data-id="' . $coupon->id . '" ' . $toggleChecked . '>
                <span class="coupon-slider"></span>
            </label>';

                $editUrl = route('coupons.edit', $coupon->id);
            $actions = '<span class="action-btn">'
                . '<a href="' . $editUrl . '" data-toggle="tooltip" title="Edit"><i class="mdi mdi-lead-pencil"></i></a>'
                . '<a href="javascript:void(0)" class="delete-btn btn-delete-coupon" data-id="' . $coupon->id . '" data-toggle="tooltip" title="Delete"><i class="mdi mdi-delete"></i></a>'
                . '</span>';

            $checkboxHtml = '<input type="checkbox" class="coupon-checkbox animate-chk" data-id="' . $coupon->id . '">';

            $rows[] = [
                $checkboxHtml,
                '<a href="' . $editUrl . '" class="font-weight-bold text-dark">' . e($code) . '</a>',
                $discountLabel,
                $storeLink,
                $privacyBadge,
                $expiryLabel,
                $toggleHtml,
                $actions,
            ];
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $rows,
        ]);
    }

    /** Show create form */
    public function create(Request $request)
    {
        $vendorId = $request->route('id');
        $vendors  = DB::table('vendors')->whereNotNull('title')->where('title', '!=', '')->orderBy('title')->get(['id', 'title']);

        return view('coupons.create', compact('vendorId', 'vendors'));
    }

    /** Store new coupon */
    public function store(Request $request)
    {
        $request->validate([
            'code'          => 'required|string|max:50',
            'discount'      => 'required|numeric|min:0',
            'discount_type' => 'required|in:Percentage,Fix Price',
            'expires_at'    => 'nullable|date',
            'description'   => 'nullable|string|max:500',
            'vendor_id'     => 'nullable|string',
            'is_public'     => 'nullable',
        ]);

        // Check duplicate code
        $exists = DB::table('coupons')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.code')) = ?", [$request->code])
            ->exists();
        if ($exists) {
            return back()->withErrors(['code' => 'This coupon code already exists.'])->withInput();
        }

        $id = Str::uuid()->toString();
        $payload = [
            'code'          => strtoupper($request->code),
            'discount'      => $request->discount,
            'discountType'  => $request->discount_type,
            'discount_type' => $request->discount_type,
            'expiresAt'     => $request->expires_at
                                ? date('Y-m-d H:i:s', strtotime($request->expires_at))
                                : null,
            'expires_at'    => $request->expires_at
                                ? date('Y-m-d H:i:s', strtotime($request->expires_at))
                                : null,
            'description'   => $request->description ?? '',
            'isPublic'      => $request->is_public ? true : false,
            'is_public'     => $request->is_public ? 1 : 0,
            'image'         => null,
            'firestore_id'  => $id,
            'data_created_at' => now()->format('Y-m-d H:i:s'),
        ];

        DB::table('coupons')->insert([
            'id'        => $id,
            'vendorID'  => $request->vendor_id ?: null,
            'isEnabled' => $request->boolean('isEnabled', true) ? 1 : 0,
            'isEnable'  => $request->boolean('isEnabled', true) ? 1 : 0,
            'payload'   => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('coupons')->with('success', 'Coupon created successfully.');
    }

    /** Show edit form */
    public function edit(string $id)
    {
        $coupon = DB::table('coupons')->where('id', $id)->first();
        if (!$coupon) {
            abort(404);
        }
        $payload = json_decode($coupon->payload ?? '{}', true) ?? [];
        $vendors = DB::table('vendors')->whereNotNull('title')->where('title', '!=', '')->orderBy('title')->get(['id', 'title']);

        return view('coupons.edit', compact('coupon', 'payload', 'vendors'));
    }

    /** Update coupon */
    public function update(Request $request, string $coupon)
    {
        $request->validate([
            'code'          => 'required|string|max:50',
            'discount'      => 'required|numeric|min:0',
            'discount_type' => 'required|in:Percentage,Fix Price',
            'expires_at'    => 'nullable|date',
            'description'   => 'nullable|string|max:500',
            'vendor_id'     => 'nullable|string',
            'is_public'     => 'nullable',
        ]);

        $existing = DB::table('coupons')->where('id', $coupon)->first();
        if (!$existing) {
            abort(404);
        }

        $oldPayload = json_decode($existing->payload ?? '{}', true) ?? [];
        $expiresAt = $request->expires_at
            ? date('Y-m-d H:i:s', strtotime($request->expires_at))
            : null;
        $newPayload = array_merge($oldPayload, [
            'code'          => strtoupper($request->code),
            'discount'      => $request->discount,
            'discountType'  => $request->discount_type,
            'discount_type' => $request->discount_type,
            'expiresAt'     => $expiresAt,
            'expires_at'    => $expiresAt,
            'description'   => $request->description ?? '',
            'isPublic'      => $request->is_public ? true : false,
            'is_public'     => $request->is_public ? 1 : 0,
        ]);

        DB::table('coupons')->where('id', $coupon)->update([
            'vendorID'   => $request->vendor_id ?: null,
            'isEnabled'  => $request->boolean('isEnabled', true) ? 1 : 0,
            'isEnable'   => $request->boolean('isEnabled', true) ? 1 : 0,
            'payload'    => json_encode($newPayload),
            'updated_at' => now(),
        ]);

        return redirect()->route('coupons')->with('success', 'Coupon updated successfully.');
    }

    /** Toggle enabled status */
    public function toggle(Request $request, string $id): JsonResponse
    {
        $coupon = DB::table('coupons')->where('id', $id)->first();
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        $newStatus = $coupon->isEnabled ? 0 : 1;
        DB::table('coupons')->where('id', $id)->update(['isEnabled' => $newStatus, 'updated_at' => now()]);

        return response()->json(['success' => true, 'enabled' => (bool) $newStatus]);
    }

    /** Delete single */
    public function destroy(Request $request, string $coupon = '')
    {
        $id = $coupon ?: $request->input('id');
        DB::table('coupons')->where('id', $id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('coupons')->with('success', 'Coupon deleted.');
    }

    /** Bulk delete */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            DB::table('coupons')->whereIn('id', $ids)->delete();
        }
        return response()->json(['success' => true]);
    }
}
