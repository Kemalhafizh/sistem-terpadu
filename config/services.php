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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
        // Only accounts on this e-mail domain may sign in with Google (empty = any).
        'sso_domain' => env('GOOGLE_SSO_DOMAIN'),
    ],

    // Firebase Cloud Messaging HTTP v1 (push notifications to the mobile app).
    // credentials: path to the service-account JSON key file, or the JSON
    // itself. project_id defaults to the one inside the key. Without
    // credentials, pushes are only logged.
    'fcm' => [
        'credentials' => env('FCM_CREDENTIALS'),
        'project_id' => env('FCM_PROJECT_ID'),
        'timeout' => (int) env('FCM_TIMEOUT', 10),
    ],

    // Fonnte WhatsApp gateway. The admin settings page (whatsapp_api_token,
    // whatsapp_sandbox_mode) takes precedence; these are the fallbacks.
    'whatsapp' => [
        'token' => env('WHATSAPP_API_TOKEN'),
        'sandbox' => (bool) env('WHATSAPP_GATEWAY_SANDBOX', true),
    ],

    'midtrans' => [
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized' => true,
        'is_3ds' => true,
    ],

];
