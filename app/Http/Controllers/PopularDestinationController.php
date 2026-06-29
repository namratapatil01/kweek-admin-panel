<?php

namespace App\Http\Controllers;

<<<<<<< HEAD

class PopularDestinationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('destinations.index');
    }
    
    public function create()
    {
        return view('destinations.create');
    }

    public function edit($id)
    {
        return view('destinations.edit')->with('id',$id);
    }

}
=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class PopularDestinationController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "popular-destinations";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
