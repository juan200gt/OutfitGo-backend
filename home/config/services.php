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

        'stripe' => [
            'secret' => env('STRIPE_SECRET'),
        ],

        'google' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URI'),
        ],
        
        'cloudinary' => [
            'url' => env('CLOUDINARY_URL'),
        ],

        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
        ],

        'replicate' => [
            'api_token' => env('REPLICATE_API_TOKEN'),
        ],

        'motor_ia' => env('MOTOR_IA', 'groq'),

    ];
