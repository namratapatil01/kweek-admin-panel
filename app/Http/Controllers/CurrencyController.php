<?php

<<<<<<< HEAD

namespace App\Http\Controllers;

class CurrencyController extends Controller
{ 


    public function __construct()
    {
        $this->middleware('auth');
    }
    
	    public function index()
    {
       return view("settings.currencies.index");
    }


  public function edit($id)
    {
    	return view('settings.currencies.edit')->with('id',$id);
    }

    public function create(){
       return view('settings.currencies.create');

    }

}
=======
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class CurrencyController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "currencies";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
