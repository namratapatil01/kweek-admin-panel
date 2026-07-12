<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use Illuminate\View\View;

class TaxController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'taxes';
    }

    public function index(): View
    {
        return view('taxes.index');
    }

    public function create(): View
    {
        return view('taxes.create');
    }

    public function edit(...$params): View
    {
        return view('taxes.edit', [
            'id' => (string) end($params),
        ]);
    }
}
