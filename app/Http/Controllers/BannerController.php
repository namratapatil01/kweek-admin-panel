<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class BannerController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	  public function index()
    {
        return view("banners.index");
    }

     public function edit($id)
    {
    	return view('banners.edit')->with('id', $id);
    }

    public function create()
    {
        return view('banners.create');
    }

}


=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class BannerController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "banner-items";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
