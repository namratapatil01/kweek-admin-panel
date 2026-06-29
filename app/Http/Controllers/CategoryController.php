<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class CategoryController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	  public function index()
    {
        return view("categories.index");
    }

     public function edit($id)
    {
    	return view('categories.edit')->with('id', $id);
    }

    public function create()
    {
        return view('categories.create');
    }

}


=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class CategoryController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "vendor-categories";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
