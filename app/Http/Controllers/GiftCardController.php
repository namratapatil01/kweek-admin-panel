<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use Illuminate\View\View;

class GiftCardController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'gift-cards';
    }

    public function index(): View
    {
        return view('gift_card.index');
    }

    public function save($id = ''): View
    {
        return view('gift_card.save', ['id' => $id]);
    }

    public function edit(...$params): View
    {
        $id = (string) end($params);

        return view('gift_card.save', ['id' => $id]);
    }
}
