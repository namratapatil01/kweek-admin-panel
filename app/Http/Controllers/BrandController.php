<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use Illuminate\View\View;

class BrandController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "brands";
    }

    public function index(): View
    {
        return view('brands.index');
    }

    public function create(): View
    {
        return view('brands.create');
    }

    public function edit(...$params): View
    {
        return view('brands.edit', [
            'id' => (string) end($params),
        ]);
    }
}
