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
    ],

    'investment_planner' => [
        'api_key' => env('INVESTMENT_PLANNER_API_KEY'),
    ],

    'service1_callback' => [
        'enabled' => env('SERVICE1_CALLBACK_ENABLED', false),
        'url' => env('SERVICE1_CALLBACK_URL', 'http://127.0.0.1:8001/api/service3/plans/callback'),
        'api_key' => env('SERVICE1_CALLBACK_API_KEY'),
        'timeout' => (int) env('SERVICE1_CALLBACK_TIMEOUT', 10),
    ],

    'service_b_analyzer' => [
        'base_url' => env('SERVICE_B_ANALYZER_BASE_URL', 'http://127.0.0.1:8002'),
        'latest_path' => env('SERVICE_B_ANALYZER_LATEST_PATH', '/api/user/analyze/auto/latest'),
        'internal_latest_path' => env('SERVICE_B_ANALYZER_INTERNAL_LATEST_PATH', '/api/internal/analyze/auto/latest'),
        'api_key' => env('SERVICE_B_ANALYZER_API_KEY', ''),
        'api_key_header' => env('SERVICE_B_ANALYZER_API_KEY_HEADER', 'x-api-key'),
        'timeout' => (int) env('SERVICE_B_ANALYZER_TIMEOUT', 10),
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
