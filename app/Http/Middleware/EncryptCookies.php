<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        'section_id',
        'service_type',
        'is_rtl',
        'admin_panel_color',
        'pagesizes',
        'default_latitude',
        'default_longitude',
    ];
}
