<?php

declare(strict_types=1);

return [
    'tables' => [
        'logs' => env('ACTIVITY_LOG_TABLE', 'activity_logs'),
        'counters' => env('ACTIVITY_COUNTER_TABLE', 'activity_counters'),
    ],

    'metadata' => [
        // Keys that should never be persisted to metadata (case-insensitive).
        'blocked_keys' => [
            'password',
            'passwd',
            'secret',
            'token',
            'authorization',
            'cookie',
        ],
    ],

    'idempotency' => [
        // Prefix used when storing idempotency keys in cache.
        'prefix' => env('ACTIVITY_IDEMPOTENCY_PREFIX', 'activity:idem:'),

        // If an action's #[Uniq(ttlSeconds: 0)] is used, this is the fallback TTL.
        'default_ttl_seconds' => (int) env('ACTIVITY_IDEMPOTENCY_DEFAULT_TTL', 300),
    ],
];
