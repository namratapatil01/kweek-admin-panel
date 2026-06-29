<?php

namespace App\Http\Controllers;

<<<<<<< HEAD

class brandController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

  
    public function brand()
    {
        return view('brands.index');
    }
    public function brandEdit($id)
    {
        return view('brands.edit')->with('id',$id);
    }

    public function brandCreate()
    {
        return view('brands.create');
    }

}
=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class BrandController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "brands";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
