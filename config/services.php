<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | External Job Aggregator Configuration (Soal 2d UTS PWS)
    |--------------------------------------------------------------------------
    |
    | Pengaturan timeout, retry, delay, dan TTL cache untuk integrasi API
    | eksternal melalui adapter (Alkindo, Hasnur, Public JSON Feeds).
    |
    */
    'aggregator' => [
        'timeout' => (int) env('AGGREGATOR_TIMEOUT', 15),
        'retry_attempts' => (int) env('AGGREGATOR_RETRY', 2),
        'retry_delay_ms' => (int) env('AGGREGATOR_RETRY_DELAY', 500),
        'cache_ttl_hours' => (int) env('AGGREGATOR_CACHE_TTL', 6),
    ],

];
