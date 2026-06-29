<?php

namespace App\Traits;

trait HasStringPrimaryKey
{
    public $incrementing = false;

    protected $keyType = 'string';
}
