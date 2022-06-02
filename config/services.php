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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'transferwise' => [
        'mode' => env('TRANSFERWISE_MODE', 'sandbox'),
        'profile' => [
            'personal' => env('TRANSFERWISE_PERSONAL_PROFILE'),
            'business' => env('TRANSFERWISE_BUSINESS_PROFILE')
        ],
        'endpoint' => env('TRANSFERWISE_MODE', 'sandbox') === 'production' ?
            'https://api.transferwise.com/v1/' : 'https://api.sandbox.transferwise.tech/v1/',
        'key' => env('TRANSFERWISE_API'),
    ],

    'adobe' => [
        'client_id' => env('ADOBE_CLIENT_ID'),
        'client_secret' => env('ADOBE_CLIENT_SECRET'),
        'state' => env('ADOBE_STATE'),
        'redirect_uri' => env('REDIRECT_URI')
    ],

];
