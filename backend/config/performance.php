<?php

return [
    'payments' => [
        'cache_ttl' => env('PAYMENTS_CACHE_TTL', 300), // 5 minutes
        'max_attempts' => env('PAYMENTS_MAX_ATTEMPTS', 3),
        'batch_size' => env('PAYMENTS_BATCH_SIZE', 100),
    ],
    
    'performance' => [
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1.0), // seconds
        'cache_driver' => env('PERFORMANCE_CACHE_DRIVER', 'redis'),
        'query_log_enabled' => env('QUERY_LOG_ENABLED', false),
    ],
    
    'security' => [
        'max_payment_amount' => env('MAX_PAYMENT_AMOUNT', 50000.00),
        'min_payment_amount' => env('MIN_PAYMENT_AMOUNT', 1.00),
        'max_daily_transactions' => env('MAX_DAILY_TRANSACTIONS', 50),
    ],
];
