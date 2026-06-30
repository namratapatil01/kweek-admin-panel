<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class RentalVehicleTypeController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "rental-vehicle-types";
    }
}
