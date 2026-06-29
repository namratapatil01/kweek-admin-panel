<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class CouponController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index($id='')
    {
        return view("coupons.index")->with('id',$id);;
    } 

    public function edit($id)
    {
        return view('coupons.edit')->with('id', $id);
    }

    public function create($id='')
    {
        return view('coupons.create')->with('id',$id);
    }

}


=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class CouponController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "coupons";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
