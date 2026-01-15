<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    */

    'table' => 'activity_logs',

    /*
    |--------------------------------------------------------------------------
    | Request context
    |--------------------------------------------------------------------------
    */

    // Header names used to resolve correlation id (first match wins)
    'correlation_headers' => [
        'X-Correlation-Id',
        'X-Request-Id',
        'X-Amzn-Trace-Id',
    ],

    // Resolve channel from request (web/api). Fallback to "unknown".
    'channel' => [
        'web_middlewares' => [
            // Typical Laravel web middlewares contain "web" group.
            // This is used only if you want to detect channel by route middleware groups.
        ],
        'api_prefixes' => ['/api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metadata sanitizer
    |--------------------------------------------------------------------------
    */

    'masked_keys' => [
        'password',
        'token',
        'secret',
        'authorization',
        'api_key',
        'access_token',
        'refresh_token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Writer behavior
    |--------------------------------------------------------------------------
    */

    // If true, writer will silently ignore DB failures (best-effort logging).
    'best_effort' => true,

];
