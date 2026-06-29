<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class ZoneController extends Controller
{
=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class ZoneController extends Controller
{
    use ProvidesMySqlCrud;

>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    public function __construct()
    {
        $this->middleware('auth');
    }

<<<<<<< HEAD
    public function index()
    {
        return view('zone.index');
    }
    public function edit($id)
    {
        return view('zone.edit')->with('id',$id);
    }

    public function create()
    {
        return view('zone.create');
    }
}
=======
    protected function moduleSlug(): string
    {
        return 'zones';
    }
}
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
