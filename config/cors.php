<?php

$origins = env('CORS_ALLOWED_ORIGINS');
$allowed = $origins === '*' || $origins === null
    ? ['*']
    : array_values(array_filter(array_map('trim', explode(',', (string) $origins))));

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowed,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', false),

];
