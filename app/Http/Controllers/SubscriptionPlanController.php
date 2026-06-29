<?php

<<<<<<< HEAD


namespace App\Http\Controllers;





use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

class SubscriptionPlanController extends Controller

{



    public function __construct()

    {

        $this->middleware('auth');

    }

    public function index()

    {

        return view("subscription_plans.index");

    }



    public function save($id='')

    {

        return view("subscription_plans.save")->with('id',$id);

    }



    public function SubscriptionPlanHistory($id='')

    {

    	    return view('subscription_plans.history')->with('id',$id);

    }
    public function currentSubscriberList($id)
    {
        return view("subscription_plans.current_subscriber", compact('id'));
    }


   

}

=======
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class SubscriptionPlanController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "subscription-plans";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
