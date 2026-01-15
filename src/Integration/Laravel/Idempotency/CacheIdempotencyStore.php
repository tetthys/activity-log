<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Idempotency;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Tetthys\ActivityLog\Contracts\IdempotencyStore;

final class CacheIdempotencyStore implements IdempotencyStore
{
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly string $prefix = 'activity:idem:',
        private readonly int $defaultTtlSeconds = 300,
    ) {}

    public function registerOnce(string $key, int $ttlSeconds = 0): bool
    {
        $ttl = $ttlSeconds > 0 ? $ttlSeconds : $this->defaultTtlSeconds;

        // Cache::add is atomic in most drivers (Redis, Memcached).
        return (bool) $this->cache->add($this->prefix . $key, 1, $ttl);
    }
}
