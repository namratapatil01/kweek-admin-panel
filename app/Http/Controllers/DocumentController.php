<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use Illuminate\Http\Request;

class DocumentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('documents.index');
    }
    public function create()
    {
        return view("documents.create");
    }
    public function edit($id)
    {
        return view("documents.edit")->with('id',$id);
=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class DocumentController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "documents";
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    }
}
