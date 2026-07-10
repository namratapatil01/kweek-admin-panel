<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class VendorsPayoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id = '')
    {
        return view('vendors_payouts.index')->with('id', $id);
    }

    public function create($id = '')
    {
        $sectionId = (string) request()->cookie('section_id', '');

        $query = DB::table('vendors')
            ->whereNotNull('title')
            ->where('title', '!=', '');

        if ($sectionId !== '') {
            $vendors = (clone $query)->where('section_id', $sectionId)->orderBy('title')->get(['id', 'title']);
            if ($vendors->isEmpty()) {
                $vendors = $query->orderBy('title')->get(['id', 'title']);
            }
        } else {
            $vendors = $query->orderBy('title')->get(['id', 'title']);
        }
        return view('vendors_payouts.create', [
            'id' => $id,
            'vendors' => $vendors,
        ]);
    }
}
