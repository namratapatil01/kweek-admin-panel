<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class AttributeController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	  public function index()
    {
        return view("attributes.index");
    }

     public function edit($id)
    {
    	return view('attributes.edit')->with('id', $id);
    }

    public function create()
    {
        return view('attributes.create');
    }

}


=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class AttributeController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "vendor-attributes";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
