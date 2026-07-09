<?php

namespace App\Http\Controllers;


class MapController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function multivendor()
    {

        return view('map.multivendor');
    }

    public function parcel()
    {

        return view('map.parcel');
    }

    public function rental()
    {

        return view('map.rental');
    }

    public function cab()
    {

        return view('map.cab');
    }

    public function getCabData(\Illuminate\Http\Request $request)
    {
        $sectionId = $request->input('section_id');
        
        $drivers = \DB::table('app_users')
            ->where('role', 'driver')
            ->where('sectionId', $sectionId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
            
        $rides = \DB::table('rides')
            ->where('sectionId', $sectionId)
            ->whereIn('status', ['In Transit', 'in_transit'])
            ->get();
            
        return response()->json([
            'drivers' => $drivers,
            'rides' => $rides
        ]);
    }

}