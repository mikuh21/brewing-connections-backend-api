<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    // ─────────────────────────────────────────
    // MAPBOX
    // ─────────────────────────────────────────
    'mapbox' => [
        'api_key'  => env('MAPBOX_API_KEY'),
        'base_url' => env('MAPBOX_BASE_URL',
                       'https://api.mapbox.com'),
    ],

    // ─────────────────────────────────────────
    // GOOGLE MAPS
    // ─────────────────────────────────────────
    'google_maps' => [
        'key'      => env('GOOGLE_MAPS_KEY'),
        'base_url' => env('GOOGLE_MAPS_BASE_URL',
                   'https://maps.googleapis.com/maps/api'),
    ],

    // ─────────────────────────────────────────
    // GOOGLE ANALYTICS 4
    // ─────────────────────────────────────────
    'ga4' => [
        'measurement_id' => env('GA4_MEASUREMENT_ID'),
        'api_secret'     => env('GA4_API_SECRET'),
        'base_url'       =>
            'https://www.google-analytics.com/mp/collect',
    ],

    // ─────────────────────────────────────────
    // BIGQUERY
    // ─────────────────────────────────────────
    'bigquery' => [
        'project_id'  => env('BIGQUERY_PROJECT_ID'),
        'dataset'     => env('BIGQUERY_DATASET'),
        'location'    => env('BIGQUERY_LOCATION',
                           'asia-southeast1'),
        'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    ],

    // ─────────────────────────────────────────
    // VERTEX AI
    // ─────────────────────────────────────────
    'vertex_ai' => [
        'project'     => env('VERTEX_AI_PROJECT'),
        'location'    => env('VERTEX_AI_LOCATION',
                           'asia-southeast1'),
        'endpoint'    => env('VERTEX_AI_ENDPOINT'),
        'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    ],

    // ─────────────────────────────────────────
    // MAIL
    // ─────────────────────────────────────────
    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    // ─────────────────────────────────────────
    // AWS
    // ─────────────────────────────────────────
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ─────────────────────────────────────────
    // SLACK
    // ─────────────────────────────────────────
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];