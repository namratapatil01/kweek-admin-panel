<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $id = null, $type = null)
    {
        $id = $id ?? @$_COOKIE['section_id'];
        $type = $type ?? @$_COOKIE['service_type'];
        $today = Carbon::today()->toDateString();
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        if (empty($fromDate) || empty($toDate)) {
            $fromDate = $today;
            $toDate = $today;
        }

        switch ($type) {
            case "cab-service":
                return view('dashboard.cab', compact('id', 'type', 'fromDate', 'toDate'));
            case "delivery-service":
                return view('dashboard.delivery', compact('id', 'type', 'fromDate', 'toDate'));
            case "ecommerce-service":
                return view('dashboard.ecommerce', compact('id', 'type', 'fromDate', 'toDate'));
            case "parcel_delivery":
                return view('dashboard.parcel', compact('id', 'type', 'fromDate', 'toDate'));
            case "rental-service":
                return view('dashboard.rental', compact('id', 'type', 'fromDate', 'toDate'));
            case "ondemand-service":
                return view('dashboard.ondemand', compact('id', 'type', 'fromDate', 'toDate'));
            default:
                return view('dashboard.delivery', compact('id', 'type', 'fromDate', 'toDate'));
        }
    }

    /**
     * Return active sections from MySQL as JSON.
     * Replaces the Firebase sections query after the Firebase → MySQL migration.
     */
    public function getSections(Request $request)
    {
        $sections = DB::table('sections')
            ->where('isActive', 1)
            ->orderBy('name', 'asc')
            ->get([
                'id', 'name', 'serviceTypeFlag', 'sectionImage',
                'color', 'isActive', 'serviceType',
            ]);

        return response()->json(['data' => $sections]);
    }

    public function storeFirebaseService(Request $request)
    {
        if (!empty($request->serviceJson) && !Storage::disk('local')->has('firebase/credentials.json')) {
            Storage::disk('local')->put('firebase/credentials.json', file_get_contents(base64_decode($request->serviceJson)));
        }
    }
}
