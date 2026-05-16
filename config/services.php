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

    'internal_notifications' => [
        'email' => env('INTERNAL_NOTIFICATIONS_EMAIL', 'info@autoinvoerenspanje.nl'),
    ],

    'rdw' => [
        'vehicle_endpoint' => env('RDW_VEHICLE_ENDPOINT', 'https://opendata.rdw.nl/resource/m9d7-ebf2.json'),
        'fuel_endpoint' => env('RDW_FUEL_ENDPOINT', 'https://opendata.rdw.nl/resource/8ys7-d773.json'),
        'app_token' => env('RDW_SOCRATA_APP_TOKEN'),
        'cache_ttl_days' => (int) env('RDW_CACHE_TTL_DAYS', 7),
        'timeout_seconds' => (int) env('RDW_HTTP_TIMEOUT', 5),
    ],

];
