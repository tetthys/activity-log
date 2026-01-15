<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

interface IdempotencyStore
{
    /**
     * Returns true if key was newly registered, false if already seen (within TTL policy).
     */
    public function registerOnce(string $key, int $ttlSeconds = 0): bool;
}
