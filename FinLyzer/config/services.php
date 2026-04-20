<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'timeout' => env('GROQ_TIMEOUT', 15),
    ],

    'analyzer' => [
        'api_key' => env('ANALYZER_API_KEY'),
        'accepted_headers' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('ANALYZER_API_KEY_HEADERS', 'x-api-key,service2_api_key'))
        ))),
    ],

    'fintrack_feed' => [
        'base_url' => env('FINTRACK_FEED_BASE_URL', 'http://127.0.0.1:8000'),
        'path' => env('FINTRACK_FEED_PATH', '/api/service2/users/{user_id}/transactions-feed'),
        'api_key' => env('FINTRACK_FEED_API_KEY', 'fintrack'),
        'api_key_header' => env('FINTRACK_FEED_API_KEY_HEADER', 'x-api-key'),
        'timeout' => env('FINTRACK_FEED_TIMEOUT', 20),
        'retry_times' => env('FINTRACK_FEED_RETRY_TIMES', 2),
        'retry_sleep_ms' => env('FINTRACK_FEED_RETRY_SLEEP_MS', 300),
        'default_user_id' => env('FINTRACK_FEED_DEFAULT_USER_ID', 2),
        'use_saved_since' => env('FINTRACK_FEED_USE_SAVED_SINCE', true),
        'since_cache_prefix' => env('FINTRACK_FEED_SINCE_CACHE_PREFIX', 'fintrack_feed_since_user_'),
        'auto_schedule_enabled' => env('FINTRACK_FEED_AUTO_SCHEDULE_ENABLED', false),
        'auto_schedule_cron' => env('FINTRACK_FEED_AUTO_SCHEDULE_CRON', '*/5 * * * *'),
    ],

    'service_c_planner' => [
        'base_url' => env('SERVICE_C_PLANNER_BASE_URL', 'http://127.0.0.1:8003'),
        'path' => env('SERVICE_C_PLANNER_PATH', '/api/internal/plan'),
        'api_key' => env('SERVICE_C_PLANNER_API_KEY', 'change-me'),
        'api_key_header' => env('SERVICE_C_PLANNER_API_KEY_HEADER', 'x-api-key'),
        'timeout' => env('SERVICE_C_PLANNER_TIMEOUT', 10),
    ],

    'logout_sync' => [
        'timeout' => (int) env('LOGOUT_SYNC_TIMEOUT', 5),
        'targets' => [
            [
                'url' => env('FINTRACK_LOGOUT_SYNC_URL', 'http://127.0.0.1:8001/api/internal/auth/logout-sync'),
                'api_key' => env('FINTRACK_LOGOUT_SYNC_API_KEY', ''),
            ],
            [
                'url' => env('FINLYZER_LOGOUT_SYNC_URL', 'http://127.0.0.1:8002/api/internal/auth/logout-sync'),
                'api_key' => env('FINLYZER_LOGOUT_SYNC_API_KEY', ''),
            ],
            [
                'url' => env('FINGOALS_LOGOUT_SYNC_URL', 'http://127.0.0.1:8003/api/internal/auth/logout-sync'),
                'api_key' => env('FINGOALS_LOGOUT_SYNC_API_KEY', ''),
            ],
        ],
    ],

];
