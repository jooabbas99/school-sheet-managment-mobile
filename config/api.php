<?php

return [
    // Base URL of the backend REST API (v1), without a trailing slash.
    'base_url' => rtrim((string) env('API_BASE_URL', 'http://10.0.2.2:8000/api/v1'), '/'),

    // Seconds.
    'timeout' => (int) env('API_TIMEOUT', 30),
    'connect_timeout' => (int) env('API_CONNECT_TIMEOUT', 8),

    // Where the bearer token is kept: auto | secure | file (see App\Services\Auth\TokenStore).
    'token_store' => env('API_TOKEN_STORE', 'file'),

    // Sent on login so tokens can be told apart on the server.
    'device_name' => env('API_DEVICE_NAME', 'android'),
];
