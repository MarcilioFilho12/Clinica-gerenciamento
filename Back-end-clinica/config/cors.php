<?php

$corsOrigins = env('CORS_ALLOWED_ORIGINS', '*');

if ($corsOrigins === '*' || $corsOrigins === null || $corsOrigins === '') {
    $allowedOrigins = ['*'];
} else {
    $allowedOrigins = array_values(array_filter(array_map(
        static fn (string $o): string => rtrim(trim($o), '/'),
        explode(',', (string) $corsOrigins)
    )));
}

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
