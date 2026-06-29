<?php

namespace App\Http\Controllers;
<<<<<<< HEAD
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{

     public function __construct()
    {
       $this->middleware('auth');
    }


	  public function index()
    {

        return view("taxes.index");
    }


  public function edit($id)
  {
      return view('taxes.edit')->with('id',$id);
  }

   public function create()
  {
      return view('taxes.create');
  }


=======

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class TaxController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "taxes";
    }
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
}
