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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways (SaaS Subscription Billing)
    |--------------------------------------------------------------------------
    | Public keys are safe to expose in checkout responses.
    | Secret keys are used only server-side for verification + signing.
    | Webhook secrets are used ONLY for validating incoming webhook payloads.
    */

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        // Paystack signs webhooks with hash_hmac('sha512', $body, $secret_key),
        // so the webhook secret == the account secret key by default.
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET', env('PAYSTACK_SECRET_KEY')),
        'verify_url' => env('PAYSTACK_VERIFY_URL', 'https://api.paystack.co/transaction/verify/'),
    ],

    'flutterwave' => [
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
        // Flutterwave webhook secret is set in the dashboard and echoed back
        // to us via the `verif-hash` header. Match it verbatim.
        'webhook_secret' => env('FLUTTERWAVE_WEBHOOK_SECRET'),
        'verify_url' => env('FLUTTERWAVE_VERIFY_URL', 'https://api.flutterwave.com/v3/transactions/verify_by_reference'),
    ],

];
