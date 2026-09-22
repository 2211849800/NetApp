<?php

return [
    'provider' => env('SUBSCRIBER_PROVIDER', 'mock'),

    'mock' => [
        'base_url' => env('MOCK_SUBSCRIBER_API_URL', 'http://127.0.0.1:8000/mock-api/v1'),
        'timeout_seconds' => (int) env('MOCK_SUBSCRIBER_TIMEOUT', 5),
    ],

    'adv' => [
        'base_url' => env('ADV_API_URL', 'https://api.adv-radius.local/v1'),
        'api_key' => env('ADV_API_KEY', ''),
        'timeout_seconds' => (int) env('ADV_TIMEOUT', 10),
    ],
];
