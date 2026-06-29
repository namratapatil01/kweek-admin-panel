<?php

return [
    'upload' => [
        'max_size_kb' => (int) env('KWEEK_UPLOAD_MAX_KB', 10240),
        'allowed_mimes' => [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'application/pdf', 'video/mp4', 'video/webm',
        ],
    ],

    'cache' => [
        'enabled' => env('KWEEK_CACHE_ENABLED', true),
        'ttl' => (int) env('KWEEK_CACHE_TTL', 300),
    ],
];
