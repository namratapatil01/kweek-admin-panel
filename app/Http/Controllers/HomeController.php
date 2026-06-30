<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Return service types for section forms (replaces Firebase services collection).
     */
    public function getServices()
    {
        $services = DB::table('services')
            ->orderBy('name')
            ->get(['id', 'name', 'flag']);

        return response()->json(['data' => $services]);
    }
}
