<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttributeController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'vendor-attributes';
    }

    /**
     * Use the classic Item Attributes UI (icon, count badge, Name/Actions table).
     */
    public function index(): View
    {
        return view('attributes.index');
    }

    public function create(): View
    {
        return view('attributes.create');
    }

    public function edit(...$params): View
    {
        $id = (string) end($params);

        return view('attributes.edit', ['id' => $id]);
    }
}
