<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class DynamicNotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("dynamic_notifications.index");
    }


    public function save($id = null)
    {
        return view('dynamic_notifications.create')->with('id', $id);
    }

}
=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class DynamicNotificationController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "dynamic-notifications";
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
