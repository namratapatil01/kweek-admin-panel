<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeliverymanController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id='')
    {
        return view('deliveryman.index')->with('id', $id);
    }
    public function create($id='')
    {
        return view("deliveryman.create")->with('id', $id);
    }
    public function edit($id)
    {
        return view("deliveryman.edit")->with('id', $id);
    }

    public function documentList($id)
    {
        return view("deliveryman.document_list")->with('id', $id);
    }

    public function documentUpload($driverId, $id)
    {
        return view("deliveryman.document_upload", compact('driverId', 'id'));
    }
}
