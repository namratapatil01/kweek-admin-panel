<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /* ------------------------------------------------------------------ */
    /*  Pages (blade views)                                               */
    /* ------------------------------------------------------------------ */

    public function index()
    {
        return view("drivers.index");
    }

    public function edit($id)
    {
        return view('drivers.edit')->with('id', $id);
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function view($id)
    {
        return view('drivers.view')->with('id', $id);
    }

    public function DocumentList($id)
    {
        return view("drivers.document_list")->with('id', $id);
    }

    public function DocumentUpload($driverId, $id)
    {
        return view("drivers.document_upload", compact('driverId', 'id'));
    }

    public function driverChat($id)
    {
        return view('drivers.chat', compact('id'));
    }

    public function fleetDrivers()
    {
        return view("fleet_drivers.index");
    }

    public function fleetDriversCreate()
    {
        return view("fleet_drivers.create");
    }

    public function fleetDriversEdit($id)
    {
        return view("fleet_drivers.edit", compact('id'));
    }

    public function fleetDriversView($id)
    {
        return view("fleet_drivers.view", compact('id'));
    }

    /* ------------------------------------------------------------------ */
    /*  AJAX — Server-side DataTable for drivers list (MySQL)             */
    /* ------------------------------------------------------------------ */

    /**
     * Returns JSON for jQuery DataTables server-side processing.
     * Replaces all Firebase queries that were in index.blade.php.
     */
    public function datatable(Request $request)
    {
        try {
            $type       = $request->input('type', 'all');          // all | approved | pending
            $sectionId  = $request->input('section_id', '');
            $serviceType = $request->input('service_type', '');
            $status     = $request->input('status', '');           // active | inactive
            $fromDate   = $request->input('from_date', '');
            $toDate     = $request->input('to_date', '');

            // DataTables params
            $draw    = intval($request->input('draw', 1));
            $start   = intval($request->input('start', 0));
            $length  = intval($request->input('length', 10));
            $search  = $request->input('search.value', '');
            $orderCol = intval($request->input('order.0.column', 5));
            $orderDir = $request->input('order.0.dir', 'desc');

            $isFleet    = filter_var($request->input('isFleet'), FILTER_VALIDATE_BOOLEAN);

            // Build base query
            $query = AppUser::drivers();
            if ($isFleet) {
                $query->whereNotNull('ownerId');
            } else {
                $query->whereNull('ownerId');
            }

            // Type filter
            if ($type === 'approved') {
                $query->approved();
            } elseif ($type === 'pending') {
                $query->pending();
            }

            // Section / service filter
            if ($serviceType && $serviceType === 'delivery-service') {
                $query->where('serviceType', $serviceType);
            } elseif ($sectionId) {
                $query->where('sectionId', $sectionId);
            }

            // Status filter
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }

            // Date filter
            if ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            // Total before search
            $totalFiltered = $query->count();

            // Search
            if ($search && strlen($search) >= 1) {
                $query->where(function ($q) use ($search) {
                    $q->where('firstName', 'LIKE', "%{$search}%")
                      ->orWhere('lastName', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('phoneNumber', 'LIKE', "%{$search}%");
                });
                $totalFiltered = $query->count();
            }

            // Orderable columns mapping
            $columns = ['', '', 'firstName', 'active', 'isActive', 'created_at', 'orderCompleted'];
            $orderByField = $columns[$orderCol] ?? 'created_at';
            if ($orderByField) {
                $query->orderBy($orderByField, $orderDir);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Paginate
            $drivers = $query->skip($start)->take($length)->get();

            // Preload service names
            $serviceNames = DB::table('services')->pluck('name', 'flag')->toArray();

            // Preload placeholder image
            $placeholderRaw = DB::table('settings')->where('id', 'placeHolderImage')->value('value');
            $placeholderImage = '';
            if ($placeholderRaw) {
                $decoded = json_decode($placeholderRaw, true);
                $placeholderImage = $decoded['image'] ?? '';
            }

            // Preload document verification status for the page
            $driverIds = $drivers->pluck('id')->toArray();
            $docVerifications = DB::table('documents_verify')
                ->whereIn('id', $driverIds)
                ->pluck('documents', 'id')
                ->toArray();

            // Preload order counts per service type
            $orderCounts = $this->getOrderCounts($driverIds);

            // Build rows
            $data = [];
            foreach ($drivers as $driver) {
                $data[] = $this->buildRow($driver, $serviceNames, $placeholderImage, $docVerifications, $orderCounts, $type);
            }

            // Count total drivers (unfiltered for this type)
            $totalQuery = AppUser::drivers();
            if ($type === 'approved') $totalQuery->approved();
            elseif ($type === 'pending') $totalQuery->pending();
            if ($serviceType && $serviceType === 'delivery-service') {
                $totalQuery->where('serviceType', $serviceType);
            } elseif ($sectionId) {
                $totalQuery->where('sectionId', $sectionId);
            }
            $totalRecords = $totalQuery->count();

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data'            => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('DriverController@datatable error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'draw'            => intval($request->input('draw', 1)),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => $e->getMessage(),
            ], 500);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Toggle active / online status (replaces Firebase update)          */
    /* ------------------------------------------------------------------ */

    public function toggleStatus(Request $request)
    {
        $id    = $request->input('id');
        $field = $request->input('field');       // 'active' or 'isActive'
        $value = filter_var($request->input('value'), FILTER_VALIDATE_BOOLEAN);

        if (!in_array($field, ['active', 'isActive'])) {
            return response()->json(['error' => 'Invalid field'], 422);
        }

        AppUser::where('id', $id)->update([$field => $value]);

        Log::info("Driver {$id} — {$field} set to " . ($value ? 'true' : 'false'));

        return response()->json(['success' => true]);
    }

    /* ------------------------------------------------------------------ */
    /*  Delete driver (replaces Firebase delete)                          */
    /* ------------------------------------------------------------------ */

    public function destroy(Request $request)
    {
        $id = $request->input('id');

        Log::info("Deleting driver: {$id}");

        // Delete related records
        DB::table('driver_payouts')->where('driverID', $id)->delete();
        DB::table('documents_verify')->where('id', $id)->delete();

        // Delete the driver
        AppUser::where('id', $id)->delete();

        return response()->json(['success' => true]);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['error' => 'No IDs provided'], 422);
        }

        Log::info("Bulk deleting drivers: " . implode(', ', $ids));

        DB::table('driver_payouts')->whereIn('driverID', $ids)->delete();
        DB::table('documents_verify')->whereIn('id', $ids)->delete();
        AppUser::whereIn('id', $ids)->delete();

        return response()->json(['success' => true]);
    }

    /* ------------------------------------------------------------------ */
    /*  Private helpers                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Get order counts for multiple drivers in bulk (single query per order table).
     */
    private function getOrderCounts(array $driverIds): array
    {
        if (empty($driverIds)) return [];

        $counts = [];
        foreach ($driverIds as $id) {
            $counts[$id] = 0;
        }

        // vendor_orders (delivery-service, ecommerce-service)
        $vo = DB::table('vendor_orders')
            ->select('driverID', DB::raw('COUNT(*) as cnt'))
            ->whereIn('driverID', $driverIds)
            ->groupBy('driverID')
            ->pluck('cnt', 'driverID')
            ->toArray();
        foreach ($vo as $did => $cnt) {
            $counts[$did] = ($counts[$did] ?? 0) + $cnt;
        }

        // rides (cab-service)
        $rides = DB::table('rides')
            ->select('driverId', DB::raw('COUNT(*) as cnt'))
            ->whereIn('driverId', $driverIds)
            ->groupBy('driverId')
            ->pluck('cnt', 'driverId')
            ->toArray();
        foreach ($rides as $did => $cnt) {
            $counts[$did] = ($counts[$did] ?? 0) + $cnt;
        }

        // rental_orders (rental-service)
        $rentals = DB::table('rental_orders')
            ->select('driverId', DB::raw('COUNT(*) as cnt'))
            ->whereIn('driverId', $driverIds)
            ->groupBy('driverId')
            ->pluck('cnt', 'driverId')
            ->toArray();
        foreach ($rentals as $did => $cnt) {
            $counts[$did] = ($counts[$did] ?? 0) + $cnt;
        }

        // parcel_orders (parcel_delivery)
        $parcels = DB::table('parcel_orders')
            ->select('driverId', DB::raw('COUNT(*) as cnt'))
            ->whereIn('driverId', $driverIds)
            ->groupBy('driverId')
            ->pluck('cnt', 'driverId')
            ->toArray();
        foreach ($parcels as $did => $cnt) {
            $counts[$did] = ($counts[$did] ?? 0) + $cnt;
        }

        return $counts;
    }

    /**
     * Get document verification icon HTML for a driver.
     */
    private function getDocumentStatusIcon(string $driverId, array $docVerifications): string
    {
        if (!isset($docVerifications[$driverId])) {
            return '';
        }

        $docsJson = $docVerifications[$driverId];
        $docs = is_string($docsJson) ? json_decode($docsJson, true) : [];

        if (empty($docs) || !is_array($docs)) {
            return '';
        }

        $approved = 0;
        $rejected = 0;
        $total = count($docs);

        foreach ($docs as $d) {
            $status = $d['status'] ?? '';
            if ($status === 'approved') $approved++;
            if ($status === 'rejected') $rejected++;
        }

        if ($approved === $total && $total > 0) {
            return '<i class="mdi mdi-verified verified-icon" data-toggle="tooltip" title="Verified"></i>';
        }
        if ($rejected > 0) {
            return '<i class="mdi mdi-close-circle unverified-icon" data-toggle="tooltip" title="Rejected" style="color:red;"></i>';
        }

        return '';
    }

    /**
     * Build a single DataTable row array for a driver.
     */
    private function buildRow($driver, $serviceNames, $placeholderImage, $docVerifications, $orderCounts, $type): array
    {
        $row = [];
        $id  = $driver->id;

        $editUrl     = $driver->ownerId ? route('fleet.drivers.edit', $id) : route('drivers.edit', $id);
        $viewUrl     = $driver->ownerId ? route('fleet.drivers.view', $id) : route('drivers.view', $id);
        $documentUrl = route('drivers.document', $id);
        $walletUrl   = route('users.walletstransaction', 'driverID=' . $id);

        $userPermissions = json_decode(session('user_permissions', '[]'), true) ?: [];

        $checkDeletePermission = false;
        if (
            ($type == 'pending' && in_array('pending.driver.delete', $userPermissions)) ||
            ($type == 'approved' && in_array('approve.driver.delete', $userPermissions)) ||
            ($type == 'all' && in_array('drivers.delete', $userPermissions))
        ) {
            $checkDeletePermission = true;
        }

        // Checkbox column
        if ($checkDeletePermission) {
            $row[] = '<input type="checkbox" id="is_open_' . $id . '" class="is_open" dataId="' . $id . '"><label class="col-3 control-label" for="is_open_' . $id . '"></label>';
        }

        // Actions column
        $actions = '<span class="action-btn">';
        $actions .= '<a href="' . $documentUrl . '" data-toggle="tooltip" title="' . trans('lang.document') . '"><i class="fa fa-file"></i></a>';
        $actions .= '<a href="' . $walletUrl . '"><i class="mdi mdi-wallet" data-toggle="tooltip" title="' . trans('lang.wallet_transaction') . '"></i></a>';
        $actions .= '<a href="' . $viewUrl . '" data-toggle="tooltip" title="' . trans('lang.view') . '"><i class="mdi mdi-eye"></i></a>';
        $actions .= '<a href="' . $editUrl . '" data-toggle="tooltip" title="' . trans('lang.edit') . '"><i class="mdi mdi-lead-pencil"></i></a>';
        if ($checkDeletePermission) {
            $actions .= '<a id="' . $id . '" data-toggle="tooltip" title="' . trans('lang.delete') . '" name="driver-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';
        $row[] = $actions;

        // Driver info column (image + name + verification icon)
        $verified = $this->getDocumentStatusIcon($id, $docVerifications);
        $photo = $driver->profilePictureURL ?: $placeholderImage;
        $name  = e($driver->firstName . ' ' . $driver->lastName);
        $row[] = '<img class="rounded" style="width:50px" src="' . e($photo) . '" alt="image" onerror="this.onerror=null;this.src=\'' . e($placeholderImage) . '\'">'
               . '<a data-url="' . $viewUrl . '" href="' . $viewUrl . '" class="redirecttopage left_space">' . $name . '</a>' . $verified;

        // Active toggle
        $activeChecked = $driver->active ? 'checked' : '';
        $row[] = '<label class="switch"><input type="checkbox" ' . $activeChecked . ' id="' . $id . '" name="isActive"><span class="slider round"></span></label>';

        // Online toggle
        $onlineChecked = $driver->isActive ? 'checked' : '';
        $row[] = '<label class="switch"><input type="checkbox" ' . $onlineChecked . ' id="' . $id . '" name="isOnline"><span class="slider round"></span></label>';

        // Date column
        if ($driver->created_at) {
            $dt = \Carbon\Carbon::parse($driver->created_at);
            $row[] = '<td class="dt-time">' . $dt->format('D M d Y') . '<br>' . $dt->format('h:i:s A') . '</td>';
        } else {
            $row[] = '';
        }

        // Total orders
        $totalOrders = $orderCounts[$id] ?? 0;
        if ($totalOrders > 0 && $driver->serviceType) {
            $url = $this->getOrderUrl($driver);
            $row[] = '<a href="' . $url . '">' . $totalOrders . '</a>';
        } else {
            $row[] = (string) $totalOrders;
        }

        return $row;
    }

    /**
     * Build order detail URL based on service type.
     */
    private function getOrderUrl($driver): string
    {
        switch ($driver->serviceType) {
            case 'cab-service':
                return route('drivers.rides', $driver->id);
            case 'rental-service':
                return route('rental_orders.driver', $driver->id);
            case 'delivery-service':
            case 'ecommerce-service':
                return route('orders', 'driverId=' . $driver->id);
            case 'parcel_delivery':
                return route('parcel_orders.driver', $driver->id);
            default:
                return 'javascript:void(0)';
        }
    }

    /**
     * Get a single driver by ID.
     */
    public function getDriver($id)
    {
        $driver = AppUser::find($id);
        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }
        $counts = $this->getOrderCounts([$id]);
        $driver->total_orders = $counts[$id] ?? 0;
        return response()->json(['data' => $driver]);
    }

    /**
     * Update a driver.
     */
    public function updateDriver(Request $request, $id)
    {
        try {
            $driver = AppUser::find($id);
            if (!$driver) {
                return response()->json(['error' => 'Driver not found'], 404);
            }

            $data = $request->only([
                'firstName', 'lastName', 'active', 'profilePictureURL',
                'carNumber', 'carMakes', 'carName', 'vehicleId',
                'rideType', 'vehicleType', 'userBankDetails', 'zoneId', 'ownerId'
            ]);

            if (isset($data['active'])) {
                $data['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            }
            if (isset($data['userBankDetails']) && is_array($data['userBankDetails'])) {
                $data['userBankDetails'] = json_encode($data['userBankDetails']);
            }

            if ($request->has('location')) {
                $loc = $request->input('location');
                $data['latitude'] = $loc['latitude'] ?? null;
                $data['longitude'] = $loc['longitude'] ?? null;
            }

            $driver->update($data);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('DriverController@updateDriver error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new driver.
     */
    public function storeDriver(Request $request)
    {
        try {
            $id = $request->input('id');
            if (empty($id)) {
                return response()->json(['error' => 'User ID is required'], 422);
            }

            $data = $request->only([
                'id', 'firstName', 'lastName', 'email', 'phoneNumber',
                'active', 'profilePictureURL', 'carNumber', 'carMakes',
                'carName', 'vehicleId', 'sectionId', 'rideType',
                'serviceType', 'vehicleType', 'userBankDetails', 'zoneId', 'ownerId'
            ]);

            $data['role'] = 'driver';
            $data['active'] = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $data['isOwner'] = false;
            $data['isDocumentVerify'] = false;
            $data['wallet_amount'] = 0;
            $data['orderCompleted'] = 0;
            
            // Handle json conversion for userBankDetails if it's an array
            if (isset($data['userBankDetails']) && is_array($data['userBankDetails'])) {
                $data['userBankDetails'] = json_encode($data['userBankDetails']);
            }

            if ($request->has('location')) {
                $loc = $request->input('location');
                $data['latitude'] = $loc['latitude'] ?? null;
                $data['longitude'] = $loc['longitude'] ?? null;
            }

            $driver = AppUser::updateOrCreate(['id' => $id], $data);

            return response()->json(['success' => true, 'data' => $driver]);
        } catch (\Exception $e) {
            Log::error('DriverController@storeDriver error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get UI meta: placeholder image + active currency from MySQL.
     */
    public function getMeta()
    {
        try {
            $placeholder = \DB::table('settings')->where('key', 'placeHolderImage')->value('value');
            if (!$placeholder) {
                $row = \DB::table('settings')->where('key', 'like', '%placeholder%')->first();
                $placeholder = $row ? ($row->value ?? '') : '';
            }

            $currency = \DB::table('currencies')->where('isActive', 1)->first();

            // Try to get defaultCountryCode from settings table
            $countryCodeRow = \DB::table('settings')->where('key', 'defaultCountryCode')->first();
            $defaultCountryCode = $countryCodeRow ? ($countryCodeRow->value ?? '') : '';

            return response()->json([
                'placeholderImage'   => $placeholder ?? '',
                'currencySymbol'     => $currency->symbol        ?? '',
                'symbolAtRight'      => (bool)($currency->symbolAtRight  ?? false),
                'decimal_degits'     => $currency->decimal_degits ?? 2,
                'defaultCountryCode' => $defaultCountryCode,
            ]);
        } catch (\Exception $e) {
            Log::error('DriverController@getMeta error: ' . $e->getMessage());
            return response()->json([
                'placeholderImage' => '', 'currencySymbol' => '',
                'symbolAtRight' => false, 'decimal_degits' => 2, 'defaultCountryCode' => '',
            ]);
        }
    }

    /**
     * Get services list from MySQL.
     */
    public function getServices()
    {
        try {
            $services = \DB::table('services')
                ->whereIn('flag', ['rental-service','delivery-service','parcel_delivery','cab-service','ecommerce-service'])
                ->get(['name', 'flag']);
            return response()->json(['services' => $services]);
        } catch (\Exception $e) {
            Log::error('DriverController@getServices error: ' . $e->getMessage());
            return response()->json(['services' => []]);
        }
    }

    /**
     * Get active sections from MySQL, optionally filtered by serviceTypeFlag.
     */
    public function getSections(Request $request)
    {
        try {
            $query = \DB::table('sections')->where('isActive', 1);
            if ($request->has('serviceTypeFlag') && $request->input('serviceTypeFlag')) {
                $query->where('serviceTypeFlag', $request->input('serviceTypeFlag'));
            }
            $sections = $query->get(['id', 'name', 'serviceTypeFlag', 'rideType']);
            return response()->json(['sections' => $sections]);
        } catch (\Exception $e) {
            Log::error('DriverController@getSections error: ' . $e->getMessage());
            return response()->json(['sections' => []]);
        }
    }

    public function getZones()
    {
        try {
            $zones = \DB::table('zones')->where('publish', 1)->orderBy('name')->get(['id', 'name']);
            return response()->json(['zones' => $zones]);
        } catch (\Exception $e) {
            return response()->json(['zones' => []]);
        }
    }

    public function getCarMakes()
    {
        try {
            $makes = \DB::table('car_makes')->orderBy('name')->get(['id', 'name']);
            return response()->json(['carMakes' => $makes]);
        } catch (\Exception $e) {
            return response()->json(['carMakes' => []]);
        }
    }

    public function getCarModels(Request $request)
    {
        try {
            $makeName = $request->input('car_make_name');
            $query = \DB::table('car_models')->orderBy('name');
            if ($makeName) {
                $make = \DB::table('car_makes')->where('name', $makeName)->first();
                if ($make) {
                    $query->where('car_make_id', $make->id);
                }
            }
            $models = $query->get(['id', 'name']);
            return response()->json(['carModels' => $models]);
        } catch (\Exception $e) {
            return response()->json(['carModels' => []]);
        }
    }

    public function getVehicleTypes(Request $request)
    {
        try {
            $serviceType = $request->input('service_type');
            $sectionId = $request->input('sectionId');
            
            if ($serviceType === 'rental-service') {
                $query = \DB::table('rental_vehicle_types');
            } else {
                $query = \DB::table('vehicle_types');
            }
            
            if ($sectionId) {
                $query->where('sectionId', $sectionId);
            }
            
            $types = $query->orderBy('name')->get(['id', 'name']);
            return response()->json(['vehicleTypes' => $types]);
        } catch (\Exception $e) {
            return response()->json(['vehicleTypes' => []]);
        }
    }

    /**
     * Add wallet amount.
     */

    public function addWallet(Request $request)
    {
        try {
            $id = $request->input('id');
            $amount = floatval($request->input('amount'));
            $note = $request->input('note', '');

            $driver = AppUser::find($id);
            if (!$driver) {
                return response()->json(['error' => 'Driver not found'], 404);
            }

            $newBalance = ($driver->wallet_amount ?? 0) + $amount;
            $driver->update(['wallet_amount' => $newBalance]);

            // Create Wallet Transaction
            $walletId = (string) \Illuminate\Support\Str::uuid();
            \App\Models\Wallet::create([
                'id'              => $walletId,
                'user_id'         => $id,
                'amount'          => $amount,
                'note'            => $note,
                'isTopUp'         => true,
                'payment_method'  => 'Wallet',
                'payment_status'  => 'success',
                'transactionUser' => 'driver',
                'date'            => now(),
            ]);

            return response()->json([
                'success' => true,
                'wallet_amount' => $newBalance
            ]);
        } catch (\Exception $e) {
            Log::error('DriverController@addWallet error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get documents verified list for driver.
     */
    public function getDocuments($id)
    {
        try {
            $driver = AppUser::find($id);
            if (!$driver) {
                return response()->json(['error' => 'Driver not found'], 404);
            }

            // Get active documents of type 'driver'
            $documents = \DB::table('documents')
                ->where('enable', 1)
                ->where('type', 'driver')
                ->get();

            // Get current verified status
            $verified = \DB::table('documents_verify')->where('id', $id)->first();
            $verifiedDocs = [];
            if ($verified && !empty($verified->documents)) {
                $verifiedDocs = json_decode($verified->documents, true) ?: [];
            }

            return response()->json([
                'driver' => $driver,
                'documents' => $documents,
                'verified' => $verifiedDocs
            ]);
        } catch (\Exception $e) {
            Log::error('DriverController@getDocuments error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle document verification approval/rejection.
     */
    public function verifyDocument(Request $request)
    {
        try {
            $id = $request->input('id'); // Driver ID
            $docId = $request->input('docId');
            $status = $request->input('status'); // 'approved' or 'rejected'

            $driver = AppUser::find($id);
            if (!$driver) {
                return response()->json(['error' => 'Driver not found'], 404);
            }

            // Find or create record in documents_verify
            $verified = \DB::table('documents_verify')->where('id', $id)->first();
            $documentsArray = [];
            if ($verified && !empty($verified->documents)) {
                $documentsArray = json_decode($verified->documents, true) ?: [];
            }

            // Update status inside array
            $updated = false;
            foreach ($documentsArray as &$doc) {
                if ($doc['documentId'] == $docId) {
                    $doc['status'] = $status;
                    $updated = true;
                    break;
                }
            }
            if (!$updated) {
                $documentsArray[] = [
                    'documentId' => $docId,
                    'status' => $status,
                    'frontImage' => '',
                    'backImage' => ''
                ];
            }

            // Save documents_verify
            if ($verified) {
                \DB::table('documents_verify')
                    ->where('id', $id)
                    ->update([
                        'documents' => json_encode($documentsArray),
                        'updated_at' => now()
                    ]);
            } else {
                \DB::table('documents_verify')
                    ->insert([
                        'id' => $id,
                        'documents' => json_encode($documentsArray),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
            }

            // Perform check to set isDocumentVerify & active on AppUser
            $this->reverifyDriverDocuments($id);

            // Refetch updated driver
            $driver = AppUser::find($id);

            return response()->json([
                'success' => true,
                'fcmToken' => $driver->fcmToken ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('DriverController@verifyDocument error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific document upload details.
     */
    public function getDocumentUploadDetails($driverId, $id)
    {
        try {
            $driver = AppUser::find($driverId);
            if (!$driver) {
                return response()->json(['error' => 'Driver not found'], 404);
            }

            $document = \DB::table('documents')->where('id', $id)->first();
            if (!$document) {
                return response()->json(['error' => 'Document template not found'], 404);
            }

            $verified = \DB::table('documents_verify')->where('id', $driverId)->first();
            $verifiedDocs = [];
            if ($verified && !empty($verified->documents)) {
                $verifiedDocs = json_decode($verified->documents, true) ?: [];
            }

            $selectedDoc = null;
            foreach ($verifiedDocs as $doc) {
                if ($doc['documentId'] == $id) {
                    $selectedDoc = $doc;
                    break;
                }
            }

            return response()->json([
                'driver' => $driver,
                'document' => $document,
                'verified_doc' => $selectedDoc
            ]);
        } catch (\Exception $e) {
            Log::error('DriverController@getDocumentUploadDetails error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Save/update a document upload.
     */
    public function saveDocumentUpload(Request $request)
    {
        try {
            $driverId = $request->input('id');
            $docId = $request->input('docId');
            $frontImage = $request->input('frontImage', '');
            $backImage = $request->input('backImage', '');
            $status = $request->input('status', 'approved');

            $driver = AppUser::find($driverId);
            if (!$driver) {
                return response()->json(['error' => 'Driver not found'], 404);
            }

            $verified = \DB::table('documents_verify')->where('id', $driverId)->first();
            $documentsArray = [];
            if ($verified && !empty($verified->documents)) {
                $documentsArray = json_decode($verified->documents, true) ?: [];
            }

            // Find or update in array
            $updated = false;
            foreach ($documentsArray as &$doc) {
                if ($doc['documentId'] == $docId) {
                    $doc['frontImage'] = $frontImage;
                    $doc['backImage'] = $backImage;
                    $doc['status'] = $status;
                    $updated = true;
                    break;
                }
            }
            if (!$updated) {
                $documentsArray[] = [
                    'documentId' => $docId,
                    'status' => $status,
                    'frontImage' => $frontImage,
                    'backImage' => $backImage
                ];
            }

            // Save documents_verify
            if ($verified) {
                \DB::table('documents_verify')
                    ->where('id', $driverId)
                    ->update([
                        'documents' => json_encode($documentsArray),
                        'updated_at' => now()
                    ]);
            } else {
                \DB::table('documents_verify')
                    ->insert([
                        'id' => $driverId,
                        'documents' => json_encode($documentsArray),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
            }

            // Re-verify driver documents status
            $this->reverifyDriverDocuments($driverId);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('DriverController@saveDocumentUpload error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check document verification criteria.
     */
    private function reverifyDriverDocuments($driverId)
    {
        // Get all active document IDs of type driver
        $enabledDocIds = \DB::table('documents')
            ->where('enable', 1)
            ->where('type', 'driver')
            ->pluck('id')
            ->toArray();

        // Get driver's verified documents
        $verified = \DB::table('documents_verify')->where('id', $driverId)->first();
        $approvedDocIds = [];
        if ($verified && !empty($verified->documents)) {
            $documentsArray = json_decode($verified->documents, true) ?: [];
            foreach ($documentsArray as $doc) {
                if (($doc['status'] ?? '') == 'approved') {
                    $approvedDocIds[] = $doc['documentId'];
                }
            }
        }

        // Check if all enabled document IDs are in approved list
        $allApproved = true;
        foreach ($enabledDocIds as $neededId) {
            if (!in_array($neededId, $approvedDocIds)) {
                $allApproved = false;
                break;
            }
        }

        $driver = AppUser::find($driverId);
        if ($driver) {
            if ($allApproved && count($enabledDocIds) > 0) {
                $driver->update([
                    'isDocumentVerify' => true,
                    'active' => true,
                    'isActive' => true
                ]);
            } else {
                $driver->update([
                    'isDocumentVerify' => false,
                    'active' => false,
                    'isActive' => false
                ]);
            }
        }
    }

    /**
     * Get Fleet Owners.
     */
    public function getOwners()
    {
        try {
            $owners = AppUser::where('isOwner', 1)
                ->whereNull('ownerId')
                ->where('role', 'driver')
                ->orderBy('firstName', 'asc')
                ->orderBy('lastName', 'asc')
                ->get();
            return response()->json(['success' => true, 'owners' => $owners]);
        } catch (\Exception $e) {
            Log::error('DriverController@getOwners error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
