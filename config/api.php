<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the API.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Batch Request Settings
    |--------------------------------------------------------------------------
    |
    | Configure settings for batch API requests.
    |
    */

    // Maximum number of requests allowed in a single batch
    'batch_max_requests' => env('API_BATCH_MAX_REQUESTS', 10),

    // Whether to enable batch request logging
    'batch_logging' => env('API_BATCH_LOGGING', true),

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API endpoints.
    |
    */

    // Default rate limit for API endpoints (requests per minute)
    'rate_limit' => env('API_RATE_LIMIT', 60),

    // Whether authenticated users have higher rate limits
    'auth_rate_limit' => env('API_AUTH_RATE_LIMIT', 300),

    /*
    |--------------------------------------------------------------------------
    | API Caching
    |--------------------------------------------------------------------------
    |
    | Configure caching for API responses.
    |
    */

    // Whether to enable API response caching
    'enable_caching' => env('API_ENABLE_CACHING', true),

    // Default cache duration in seconds
    'cache_duration' => env('API_CACHE_DURATION', 300),

    // Endpoints that should never be cached
    'no_cache_endpoints' => [
        '/api/cart',
        '/api/cart/*',
        '/api/checkout',
        '/api/checkout/*',
        '/api/user',
        '/api/user/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Versioning
    |--------------------------------------------------------------------------
    |
    | Configure API versioning.
    |
    */

    // Current default API version
    'default_version' => env('API_DEFAULT_VERSION', 'v1'),

    // Whether to require version in URL
    'require_version' => env('API_REQUIRE_VERSION', false),

    /*
    |--------------------------------------------------------------------------
    | API Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure performance monitoring for API endpoints.
    |
    */

    // Whether to log slow API requests
    'log_slow_requests' => env('API_LOG_SLOW_REQUESTS', true),

    // Threshold in milliseconds for slow request logging
    'slow_request_threshold' => env('API_SLOW_REQUEST_THRESHOLD', 1000),
]; 