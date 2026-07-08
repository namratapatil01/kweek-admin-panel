<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = VendorCategory::all();
        return view("stores.index", compact('categories'));
    }

    public function create()
    {
        $categories = VendorCategory::all();
        return view('stores.create', compact('categories'));
    }

    public function edit($id)
    {
        $categories = VendorCategory::all();
        return view('stores.edit')->with(compact('id', 'categories'));
    }

    public function view($id)
    {
        $vendor = Vendor::with('section')->find($id);
        if (!$vendor) {
            return redirect()->route('stores');
        }

        $total_orders = DB::table('vendor_orders')->where('vendorID', $id)->count();
        $total_items = DB::table('vendor_products')->where('vendorID', $id)->count();
        $total_earnings = DB::table('vendor_orders')->where('vendorID', $id)->where('status', 'Order Completed')->sum('subTotal') ?? 0;
        $total_payment = DB::table('payouts')->where('vendorID', $id)->sum('amount') ?? 0;

        $user = \App\Models\AppUser::where('vendorID', $id)->where('role', 'vendor')->first();

        return view('stores.view')->with([
            'id' => $id, 
            'vendor' => $vendor,
            'user' => $user,
            'total_orders' => $total_orders,
            'total_items' => $total_items,
            'total_earnings' => $total_earnings,
            'total_payment' => $total_payment,
        ]);
    }

    /**
     * MySQL-backed DataTables endpoint for the Stores page.
     * Shows all vendors (no section_id filter) with photo, title, phone, date, item count, order count.
     */
    public function datatable(Request $request): JsonResponse
    {
        $draw      = (int) $request->input('draw', 1);
        $start     = (int) $request->input('start', 0);
        $length    = (int) $request->input('length', 10);
        $search    = trim($request->input('search.value', ''));
        $orderDir  = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        // Placeholder image from settings
        $placeholderImage = asset('images/default_user.png');
        $placeholderRaw   = DB::table('settings')->where('id', 'placeHolderImage')->value('value');
        if ($placeholderRaw) {
            $decoded = json_decode($placeholderRaw, true);
            if (!empty($decoded['image'])) {
                $placeholderImage = $decoded['image'];
            }
        }

        $query = Vendor::query();

        // Search by title or phonenumber
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('phonenumber', 'like', '%' . $search . '%');
            });
        }

        $categoryId = $request->input('category_id');
        if (!empty($categoryId)) {
            $query->where('categoryID', 'like', '%' . $categoryId . '%');
        }

        $total = (clone $query)->count();

        $vendors = $query
            ->orderBy('created_at', $orderDir)
            ->skip($start)
            ->take($length > 0 ? $length : 15)
            ->get(['id', 'title', 'photo', 'phonenumber', 'reststatus', 'created_at', 'createdAt', 'payload']);

        // Get item and order counts in bulk for this page
        $vendorIds = $vendors->pluck('id')->toArray();

        $itemCounts = DB::table('vendor_products')
            ->whereIn('vendorID', $vendorIds)
            ->selectRaw('vendorID, COUNT(*) as cnt')
            ->groupBy('vendorID')
            ->pluck('cnt', 'vendorID');

        $orderCounts = DB::table('vendor_orders')
            ->whereIn('vendorID', $vendorIds)
            ->selectRaw('vendorID, COUNT(*) as cnt')
            ->groupBy('vendorID')
            ->pluck('cnt', 'vendorID');

        $rows = [];
        foreach ($vendors as $vendor) {
            $id        = $vendor->id;
            $editUrl   = route('stores.edit', $id);
            $viewUrl   = route('stores.view', $id);
            $photo     = $vendor->photo ?: $placeholderImage;
            $title     = e($vendor->title ?? '');
            $phone     = e($vendor->phonenumber ?? '');
            $date      = '';
            if ($vendor->created_at) {
                $date = $vendor->created_at->format('D M d Y') . '<br>' . $vendor->created_at->format('g:i:s A');
            } elseif ($vendor->createdAt) {
                try {
                    $dt   = \Carbon\Carbon::parse($vendor->createdAt);
                    $date = $dt->format('D M d Y') . '<br>' . $dt->format('g:i:s A');
                } catch (\Throwable $e) {}
            }
            $items  = $itemCounts[$id] ?? 0;
            $orders = $orderCounts[$id] ?? 0;

            $statusBadge = $vendor->reststatus
                ? '<span class="badge badge-success">Open</span>'
                : '<span class="badge badge-secondary">Closed</span>';

            // Check delete permission
            $canDelete = false;
            $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];
            if (in_array('stores.delete', $permissions, true)) {
                $canDelete = true;
            }
            $user = auth()->user();
            if ($user && (int) $user->role_id === 1) {
                $canDelete = true;
            }

            // Check copy permission
            $canCopy = false;
            if (in_array('stores.copy', $permissions, true)) {
                $canCopy = true;
            }
            if ($user && (int) $user->role_id === 1) {
                $canCopy = true;
            }

            $row = [];

            // Checkbox column
            $row[] = $canDelete
                ? '<span class="delete-all"><input type="checkbox" id="is_open_' . $id . '" class="is_open" data-id="' . $id . '"><label class="col-3 control-label" for="is_open_' . $id . '"></label></span>'
                : '';

            // Actions column — circular coloured buttons matching Sections page style
            $walletUrl = route('users.walletstransaction', 'storeID=' . $id);
            $actions  = '<div class="store-action-btns">';
            $actions .= '<a href="' . $walletUrl . '" class="btn-circle-store btn-circle-wallet" data-toggle="tooltip" title="Wallet History"><i class="mdi mdi-wallet"></i></a>';
            if ($canCopy) {
                $authorId = $vendor->payload['author'] ?? '';
                $actions .= '<a href="javascript:void(0)" vendor_id="' . $id . '" author="' . $authorId . '" name="vendor-clone" class="btn-circle-store btn-circle-copy" data-toggle="tooltip" title="Copy"><i class="mdi mdi-content-copy"></i></a>';
            }
            $actions .= '<a href="' . $viewUrl  . '" class="btn-circle-store btn-circle-view"   data-toggle="tooltip" title="View"><i class="mdi mdi-eye"></i></a>';
            $actions .= '<a href="' . $editUrl  . '" class="btn-circle-store btn-circle-edit"   data-toggle="tooltip" title="Edit"><i class="mdi mdi-lead-pencil"></i></a>';
            if ($canDelete) {
                $actions .= '<a id="' . $id . '" name="delete-btn" class="btn-circle-store btn-circle-delete do_not_delete" href="javascript:void(0)" data-toggle="tooltip" title="Delete"><i class="mdi mdi-delete"></i></a>';
            }
            $actions .= '</div>';
            $row[] = $actions;

            // Store Info column (photo + title)
            $row[] = '<img alt="" style="width:70px;height:70px;object-fit:cover;" src="' . e($photo) . '" onerror="this.onerror=null;this.src=\'' . e($placeholderImage) . '\'">'
                   . '<a href="' . $viewUrl . '" class="redirecttopage left_space">' . $title . '</a>';

            // Phone
            $row[] = $phone;

            // Date
            $row[] = '<span class="dt-time">' . $date . '</span>';

            // Items count
            $itemsUrl  = route('vendors.items', $id);
            $row[] = $items > 0 ? '<a href="' . $itemsUrl . '">' . $items . '</a>' : (string) $items;

            // Orders count
            $ordersUrl = route('vendors.orders', $id);
            $row[] = $orders > 0 ? '<a href="' . $ordersUrl . '">' . $orders . '</a>' : (string) $orders;

            $rows[] = $row;
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $rows,
        ]);
    }

    /**
     * Clone vendor (store) along with new owner user and items/products.
     */
    public function clone(Request $request): JsonResponse
    {
        $user = auth()->user();
        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];
        $canCopy = in_array('stores.copy', $permissions, true) || ($user && (int) $user->role_id === 1);

        if (!$canCopy) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'vendor_id' => 'required|string',
            'user_name' => 'required|string|max:255',
            'user_last_name' => 'required|string|max:255',
            'vendor_title' => 'required|string|max:255',
            'user_email' => 'required|email',
            'user_password' => 'required|string|min:6',
        ]);

        // Check if email already exists in app_users
        $emailExists = DB::table('app_users')->where('email', $request->user_email)->exists();
        if ($emailExists) {
            return response()->json(['message' => 'Email is already taken.'], 422);
        }

        $origVendor = Vendor::find($request->vendor_id);
        if (!$origVendor) {
            return response()->json(['message' => 'Original vendor not found.'], 404);
        }

        try {
            DB::beginTransaction();

            $origOwnerId = $origVendor->payload['author'] ?? null;
            $origOwner = null;
            if ($origOwnerId) {
                $origOwner = \App\Models\AppUser::find($origOwnerId);
            }

            // Create new Owner AppUser
            $newOwnerId = (string) \Illuminate\Support\Str::uuid();
            $newVendorId = (string) \Illuminate\Support\Str::uuid();

            $newOwnerData = [
                'id' => $newOwnerId,
                'firstName' => $request->user_name,
                'lastName' => $request->user_last_name,
                'email' => $request->user_email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->user_password),
                'role' => 'vendor',
                'active' => true,
                'isActive' => true,
                'isOwner' => true,
                'vendorID' => $newVendorId,
                'createdAt' => now(),
            ];

            if ($origOwner) {
                $newOwnerData['countryCode'] = $origOwner->countryCode ?? '';
                $newOwnerData['zoneId'] = $origOwner->zoneId ?? null;
                $newOwnerData['settings'] = $origOwner->settings ?? null;
                $newOwnerData['payload'] = $origOwner->payload ?? null;
            }

            $newOwner = \App\Models\AppUser::create($newOwnerData);

            // Clone Vendor
            $newVendorData = $origVendor->toArray();
            $newVendorData['id'] = $newVendorId;
            $newVendorData['title'] = $request->vendor_title;
            $newVendorData['createdAt'] = now();
            $newVendorData['walletAmount'] = 0;
            $newVendorData['reviewsSum'] = 0;
            $newVendorData['reviewsCount'] = 0;

            $payload = $origVendor->payload ?? [];
            $payload['author'] = $newOwnerId;
            $payload['authorName'] = $request->user_name . ' ' . $request->user_last_name;
            $payload['walletAmount'] = 0;
            $payload['reviewsSum'] = 0;
            $payload['reviewsCount'] = 0;
            $newVendorData['payload'] = $payload;

            Vendor::create($newVendorData);

            // Clone products
            $products = \App\Models\VendorProduct::where('vendorID', $origVendor->id)->get();
            foreach ($products as $product) {
                $newProductData = $product->toArray();
                $newProductData['id'] = (string) \Illuminate\Support\Str::uuid();
                $newProductData['vendorID'] = $newVendorId;
                $newProductData['createdAt'] = now();
                
                $productPayload = $product->payload ?? [];
                $productPayload['vendorID'] = $newVendorId;
                $newProductData['payload'] = $productPayload;

                \App\Models\VendorProduct::create($newProductData);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Vendor cloned successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error cloning vendor: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to clone vendor: ' . $e->getMessage()], 500);
        }
    }
}
