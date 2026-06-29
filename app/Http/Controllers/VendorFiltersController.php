<?php

namespace App\Http\Controllers;

<<<<<<< HEAD

class VendorFiltersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('vendor_filters.index');
    }


    public function edit($id)
    {
        
        return view('vendor_filters.edit')->with('id',$id);
    }

    public function create()
    {
        return view('vendor_filters.create');
    }    
=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class VendorFiltersController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "vendor-filters";
    }
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
}
