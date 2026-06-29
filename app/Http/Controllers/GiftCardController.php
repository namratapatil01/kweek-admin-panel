<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
class GiftCardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view("gift_card.index");
    }

    public function save($id="")
    {
        return view('gift_card.save')->with('id', $id);
    }
    public function edit($id)
    {
        return view('gift_card.save')->with('id', $id);
    }

=======
use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class GiftCardController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "gift-cards";
    }
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
}
