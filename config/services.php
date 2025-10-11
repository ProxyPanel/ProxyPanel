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

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => '',
    ],

    'twitter-oauth-2' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => '',
    ],

    'linkedin-openid' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => '',
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => '',
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => '',
    ],

    'gitlab' => [
        'client_id' => env('GITLAB_CLIENT_ID'),
        'client_secret' => env('GITLAB_CLIENT_SECRET'),
        'redirect' => '',
    ],

    'bitbucket' => [
        'client_id' => env('BITBUCKET_CLIENT_ID'),
        'client_secret' => env('BITBUCKET_CLIENT_SECRET'),
        'redirect' => '',
    ],

    'slack' => [
        'client_id' => env('SLACK_CLIENT_ID'),
        'client_secret' => env('SLACK_CLIENT_SECRET'),
        'redirect' => '',
    ],

    'telegram' => [
        'bot' => env('TELEGRAM_BOT_NAME'),  // The bot's username
        'client_id' => null,
        'client_secret' => env('TELEGRAM_TOKEN'),
        'redirect' => '/oauth/telegram/redirect',
    ],

    'ip' => [
        'baidu_ak' => env('BAIDU_APP_AK'),
        'ipinfo_token' => env('IPINFO_ACCESS_TOKEN'),
        'IP2Location_key' => env('IP2LOCATION_API_KEY'),
        'ipApiCom_acess_key' => env('IP_API_ACCESS_KEY'),
        'ip-api_key' => env('IP_API_KEY'),
        'ipdata_key' => env('IPDATA_API_KEY'),
        'bjjii_key' => env('BJJII_KEY'),
    ],

    'probe' => [
        'domestic' => env('PROBE_SERVERS_DOMESTIC'),
        'foreign' => env('PROBE_SERVERS_FOREIGN'),
    ],

    'currency' => [
        'exchangerate-api_key' => env('EXCAHNGERATE_API_KEY'),
        'apiLayer_key' => env('API_LAYER_API_KEY'),
        'it120_key' => env('IT120_KEY'),
    ],

];
