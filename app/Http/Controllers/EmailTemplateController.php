<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'email-templates';
    }

    public function index(): View
    {
        return view('email_templates.index');
    }

    public function create(): View
    {
        return view('email_templates.save', ['id' => '']);
    }

    public function edit(...$params): View|RedirectResponse
    {
        $id = (string) end($params);

        return view('email_templates.save', ['id' => $id]);
    }
}
