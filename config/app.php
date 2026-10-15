<?php

return [
    'name' => env('APP_NAME', 'FleetLogix'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'America/Mexico_City'),
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
];
