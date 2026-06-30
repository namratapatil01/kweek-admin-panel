<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class ReviewAttributeController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "review-attributes";
    }
}
