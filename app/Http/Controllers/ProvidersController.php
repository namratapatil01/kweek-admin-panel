<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Support\PayloadMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProvidersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("providers.index");
    }

    public function create()
    {
        return view("providers.create");
    }

    public function edit($id)
    {
        return view('providers.edit')->with('id', $id);
    }

    public function view($id)
    {
    	return view('providers.view')->with('id', $id);
    }
   
    /**
     * Store a new provider.
     */
    public function storeProvider(Request $request)
    {
        try {
            $id = $request->input('id');
            if (empty($id)) {
                return response()->json(['error' => 'User ID is required'], 422);
            }

            $data = $request->only([
                'id', 'firstName', 'lastName', 'email', 'phoneNumber',
                'active', 'profilePictureURL', 'section_id', 'userBankDetails',
                'adminCommission', 'subscription_plan', 'subscriptionPlanId', 'subscriptionExpiryDate'
            ]);

            $data['role'] = 'provider';
            $data['active'] = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $data['isActive'] = $data['active'];
            $data['wallet_amount'] = 0;
            $data['reviewsCount'] = 0;
            $data['reviewsSum'] = 0;

            if ($request->has('password') && $request->input('password')) {
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

            $mapped = PayloadMapper::map($data, $fillable, ['payload']);
            $attributes = $mapped['attributes'];

            if (!empty($mapped['overflow'])) {
                $existing = is_array($attributes['payload'] ?? null) ? $attributes['payload'] : [];
                $attributes['payload'] = array_merge($existing, $mapped['overflow']);
            }

            $provider = AppUser::updateOrCreate(['id' => $id], $attributes);

            return response()->json(['success' => true, 'data' => $provider]);
        } catch (\Exception $e) {
            Log::error('ProvidersController@storeProvider error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}


