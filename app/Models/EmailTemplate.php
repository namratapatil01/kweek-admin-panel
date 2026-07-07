<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class EmailTemplate extends KweekModel
{
    protected $table = "email_templates";
    protected $casts = [
        'isSendToAdmin' => 'boolean',
        'payload' => 'array',
    ];
}
