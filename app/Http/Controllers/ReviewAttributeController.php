<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class ReviewAttributeController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	  public function index()
    {
        return view("reviewattributes.index");
    }

     public function edit($id)
    {
    	return view('reviewattributes.edit')->with('id', $id);
    }

    public function create()
    {
        return view('reviewattributes.create');
    }

}


=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class ReviewAttributeController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "review-attributes";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
