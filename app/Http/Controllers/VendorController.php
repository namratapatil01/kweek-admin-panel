<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class VendorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	public function index()
    {
        return view("vendors.index");
    }

    public function create(){

        return view('vendors.create');  
    }

    public function edit($id)
    {
    	return view('vendors.edit')->with('id',$id);
    }
    
    public function DocumentList($id)
    {
        return view("vendors.document_list")->with('id', $id);
    }

    public function DocumentUpload($ownerId, $id)
    {
        return view("vendors.document_upload", compact('ownerId', 'id'));
=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class VendorController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "vendors";
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    }
}
