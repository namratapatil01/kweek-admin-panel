<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdvertisementsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $id = null)
    {
        return view('advertisements.index', [
            'vendorId' => $id,
        ]);
    }

    public function requested()
    {
        return view('advertisements.requested_advertisement');
    }

    public function create(Request $request, $id = null)
    {
        $vendors = DB::table('vendors')
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->orderBy('title')
            ->get(['id', 'title', 'photo', 'phonenumber']);

        return view('advertisements.create', [
            'vendors' => $vendors,
            'vendorId' => $id ?: $request->query('id'),
        ]);
    }

    public function edit($id)
    {
        $ad = DB::table('advertisements')->where('id', $id)->first();
        if (! $ad) {
            abort(404);
        }

        $payload = json_decode($ad->payload ?? '{}', true) ?? [];
        $vendors = DB::table('vendors')
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->orderBy('title')
            ->get(['id', 'title', 'photo', 'phonenumber']);

        return view('advertisements.edit', [
            'ad' => $ad,
            'payload' => $payload,
            'vendors' => $vendors,
        ]);
    }

    public function view($id)
    {
        $ad = DB::table('advertisements')->where('id', $id)->first();
        if (! $ad) {
            abort(404);
        }

        return view('advertisements.view', [
            'id' => $id,
            'ad' => $ad,
            'payload' => json_decode($ad->payload ?? '{}', true) ?? [],
        ]);
    }

    public function chat($id)
    {
        return view('advertisements.chat', ['id' => $id]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim($request->input('search.value', ''));
        $vendorId = $request->input('vendor_id', '');
        $sectionId = $request->input('section_id') ?: $request->cookie('section_id');

        $query = DB::table('advertisements');

        if ($vendorId !== '') {
            $query->where('vendorId', $vendorId);
        }

        // Include ads for current section OR ads with no section assigned (legacy imports).
        if ($sectionId) {
            $query->where(function ($q) use ($sectionId) {
                $q->where('sectionId', $sectionId)
                    ->orWhereNull('sectionId')
                    ->orWhere('sectionId', '')
                    ->orWhereRaw("JSON_VALID(payload) = 1 AND JSON_UNQUOTE(JSON_EXTRACT(payload, '$.sectionId')) = ?", [$sectionId])
                    ->orWhereRaw("JSON_VALID(payload) = 1 AND JSON_EXTRACT(payload, '$.sectionId') IS NULL");
            });
        }

        // Show approved/running ads on main list, exclude pending requests if status filter set
        $statusFilter = $request->input('status');
        if ($statusFilter) {
            $query->where(function ($q) use ($statusFilter) {
                $q->whereRaw("JSON_VALID(payload) = 1 AND JSON_UNQUOTE(JSON_EXTRACT(payload, '$.status')) = ?", [$statusFilter])
                    ->orWhere('title', $statusFilter);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereRaw("JSON_VALID(payload) = 1 AND JSON_UNQUOTE(JSON_EXTRACT(payload, '$.description')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_VALID(payload) = 1 AND JSON_UNQUOTE(JSON_EXTRACT(payload, '$.type')) LIKE ?", ["%{$search}%"]);
            });
        }

        $total = (clone $query)->count();
        $ads = $query->orderByDesc('created_at')->skip($start)->take($length > 0 ? $length : 10)->get();

        $vendorIds = $ads->pluck('vendorId')->filter()->unique()->values()->toArray();
        $vendors = DB::table('vendors')->whereIn('id', $vendorIds)->get(['id', 'title', 'photo', 'phonenumber'])
            ->keyBy('id');

        $rows = [];
        foreach ($ads as $ad) {
            $payload = json_decode($ad->payload ?? '{}', true) ?? [];
            $vendor = $vendors[$ad->vendorId] ?? null;

            $title = $ad->title ?: ($payload['title'] ?? '—');
            $type = $payload['type'] ?? '';
            $typeLabel = $type === 'video_promotion'
                ? 'Video Promotion'
                : ($type === 'restaurant_promotion' ? 'Store Promotion' : ($type ?: '—'));

            $startDate = $payload['startDate'] ?? null;
            $endDate = $payload['endDate'] ?? null;
            $duration = '—';
            if ($startDate || $endDate) {
                try {
                    $from = $startDate ? date('F j, Y', strtotime($startDate)) : '';
                    $to = $endDate ? date('F j, Y', strtotime($endDate)) : '';
                    $duration = trim($from . ($from && $to ? ' - ' : '') . $to);
                } catch (\Throwable $e) {
                    $duration = trim(($startDate ?? '') . ' - ' . ($endDate ?? ''));
                }
            }

            $status = $payload['status'] ?? 'pending';
            $isPaused = ! empty($payload['isPaused']);
            $now = now();
            $running = false;
            if ($status === 'approved' && ! $isPaused) {
                $startOk = ! $startDate || strtotime($startDate) <= $now->timestamp;
                $endOk = ! $endDate || strtotime($endDate) >= $now->timestamp;
                $running = $startOk && $endOk;
            }

            if ($isPaused) {
                $statusBadge = '<span class="badge badge-warning rounded-pill px-3 py-2">Paused</span>';
            } elseif ($running) {
                $statusBadge = '<span class="badge badge-info rounded-pill px-3 py-2" style="background:#20c997;">Running</span>';
            } elseif ($status === 'approved') {
                $statusBadge = '<span class="badge badge-success rounded-pill px-3 py-2">Approved</span>';
            } elseif ($status === 'pending') {
                $statusBadge = '<span class="badge badge-secondary rounded-pill px-3 py-2">Pending</span>';
            } else {
                $statusBadge = '<span class="badge badge-danger rounded-pill px-3 py-2">' . e(ucfirst($status)) . '</span>';
            }

            $priority = $payload['priority'] ?? 'N/A';

            $storeHtml = '—';
            if ($vendor) {
                $photo = $vendor->photo ?: asset('images/default_user.png');
                $storeHtml = '<div class="d-flex align-items-center">'
                    . '<img src="' . e($photo) . '" class="rounded-circle mr-2" style="width:40px;height:40px;object-fit:cover;" onerror="this.src=\'' . asset('images/default_user.png') . '\'">'
                    . '<div><div class="font-weight-bold">' . e($vendor->title) . '</div>'
                    . '<small class="text-muted">' . e($vendor->phonenumber ?? '') . '</small></div></div>';
            }

            $editUrl = route('advertisements.edit', $ad->id);
            $viewUrl = route('advertisements.view', $ad->id);
            $chatUrl = route('advertisement.chat', $ad->id);

            $actions = '<span class="action-btn ad-actions">'
                . '<a href="' . $chatUrl . '" class="btn-ad-action" title="Chat"><i class="fa fa-commenting"></i></a>'
                . '<a href="' . $viewUrl . '" class="btn-ad-action" title="View"><i class="mdi mdi-eye"></i></a>'
                . '<a href="' . $editUrl . '" class="btn-ad-action" title="Edit"><i class="mdi mdi-lead-pencil"></i></a>'
                . '<a href="javascript:void(0)" class="btn-ad-action btn-delete-ad" data-id="' . e($ad->id) . '" title="Delete"><i class="mdi mdi-delete"></i></a>'
                . '<a href="javascript:void(0)" class="btn-ad-action btn-copy-ad" data-id="' . e($ad->id) . '" title="Copy"><i class="mdi mdi-content-copy"></i></a>'
                . '<a href="' . $viewUrl . '" class="btn-ad-action" title="Details"><i class="mdi mdi-target"></i></a>'
                . '</span>';

            $rows[] = [
                '<input type="checkbox" class="ad-checkbox" data-id="' . e($ad->id) . '">',
                '<a href="' . $viewUrl . '" class="font-weight-bold text-dark">' . e(\Illuminate\Support\Str::limit($title, 18)) . '</a>',
                $storeHtml,
                e($typeLabel),
                e($duration),
                $statusBadge,
                e((string) $priority),
                $actions,
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vendorId' => 'required|string',
            'priority' => 'nullable',
            'type' => 'required|in:restaurant_promotion,video_promotion',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
        ]);

        $id = (string) Str::uuid();
        $coverImage = $this->storeUpload($request, 'coverImage');
        $profileImage = $this->storeUpload($request, 'profileImage');
        $video = $this->storeUpload($request, 'video');

        $startDate = $request->startDate ? date('Y-m-d H:i:s', strtotime($request->startDate)) : now()->format('Y-m-d H:i:s');
        $endDate = $request->endDate ? date('Y-m-d 23:59:59', strtotime($request->endDate)) : null;

        $payload = [
            'title' => $request->title,
            'description' => $request->description ?? '',
            'type' => $request->type,
            'priority' => $request->priority ?: 'N/A',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'coverImage' => $coverImage ?: '',
            'profileImage' => $profileImage ?: '',
            'video' => $video ?: null,
            'showReview' => $request->boolean('showReview'),
            'showRating' => $request->boolean('showRating'),
            'status' => 'approved',
            'paymentStatus' => true,
            'isPaused' => false,
            'vendorId' => $request->vendorId,
            'sectionId' => $request->input('sectionId') ?: $request->cookie('section_id'),
        ];

        DB::table('advertisements')->insert([
            'id' => $id,
            'title' => $request->title,
            'vendorId' => $request->vendorId,
            'sectionId' => $payload['sectionId'],
            'isEnabled' => 1,
            'isEnable' => 1,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('advertisements')->with('success', 'Advertisement created successfully.');
    }

    public function update(Request $request, $id)
    {
        $existing = DB::table('advertisements')->where('id', $id)->first();
        if (! $existing) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vendorId' => 'required|string',
            'priority' => 'nullable',
            'type' => 'required|in:restaurant_promotion,video_promotion',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
        ]);

        $old = json_decode($existing->payload ?? '{}', true) ?? [];
        $coverImage = $this->storeUpload($request, 'coverImage') ?: ($old['coverImage'] ?? '');
        $profileImage = $this->storeUpload($request, 'profileImage') ?: ($old['profileImage'] ?? '');
        $video = $this->storeUpload($request, 'video') ?: ($old['video'] ?? null);

        $payload = array_merge($old, [
            'title' => $request->title,
            'description' => $request->description ?? '',
            'type' => $request->type,
            'priority' => $request->priority ?: 'N/A',
            'startDate' => $request->startDate ? date('Y-m-d H:i:s', strtotime($request->startDate)) : ($old['startDate'] ?? null),
            'endDate' => $request->endDate ? date('Y-m-d 23:59:59', strtotime($request->endDate)) : ($old['endDate'] ?? null),
            'coverImage' => $coverImage,
            'profileImage' => $profileImage,
            'video' => $video,
            'showReview' => $request->boolean('showReview'),
            'showRating' => $request->boolean('showRating'),
            'vendorId' => $request->vendorId,
        ]);

        DB::table('advertisements')->where('id', $id)->update([
            'title' => $request->title,
            'vendorId' => $request->vendorId,
            'payload' => json_encode($payload),
            'updated_at' => now(),
        ]);

        return redirect()->route('advertisements')->with('success', 'Advertisement updated successfully.');
    }

    public function destroy(Request $request, $id = null): JsonResponse
    {
        $ids = $request->input('ids', []);
        if ($id) {
            $ids[] = $id;
        }
        if ($request->filled('id')) {
            $ids[] = $request->input('id');
        }
        $ids = array_values(array_filter(array_unique($ids)));
        if ($ids !== []) {
            DB::table('advertisements')->whereIn('id', $ids)->delete();
        }

        return response()->json(['success' => true]);
    }

    public function sendNotification(Request $request)
    {
        return response()->json(['success' => true]);
    }

    protected function storeUpload(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $path = $file->store('advertisements', 'public');

        return Storage::disk('public')->url($path);
    }
}
