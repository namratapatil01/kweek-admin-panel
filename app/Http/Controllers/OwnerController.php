<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class OwnerController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
	public function index()
    {
        $placeholderImage = $this->placeholderImage();

        return view('owners.index', compact('placeholderImage'));
    }
    public function create()
    {
    	return view('owners.create');
    }
    public function edit($id)
    {
    	return view('owners.edit')->with('id', $id);
    }
    public function view($id)
    {
        return view('owners.view')->with('id', $id);
    }
    public function ownerDocuments($id)
    {
        return view('owners.documentIndex', compact('id'));
    }
    public function ownerDocumentUpload($ownerId, $id)
    {
        return view('owners.documentUpload', compact('ownerId', 'id'));
    }
    public function driverList($id)
    {
        return view('owners.driver_list')->with('id', $id);
    }

    protected function placeholderImage(): string
    {
        $placeholderRaw = DB::table('settings')->where('id', 'placeHolderImage')->value('value');
        if (! $placeholderRaw) {
            return '';
        }

        $decoded = json_decode($placeholderRaw, true);

        return is_array($decoded) ? ($decoded['image'] ?? '') : '';
    }
}


