<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class SectionController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	  public function index()
    {
        return view("section.index");
    }

     public function edit($id)
    {
    	return view('section.edit')->with('id', $id);
    }

    public function create()
    {
        return view('section.create');
    }

}


=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class SectionController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "sections";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
