<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

interface CounterStore
{
    /**
     * @param array<string, string|null> $keyParts
     */
    public function increment(
        string $action,
        string $dimension,
        string $bucket,
        string $bucketKey,
        array $keyParts,
        int $by = 1,
    ): void;

    /**
     * @param array<string, string|null> $keyParts
     */
    public function get(
        string $action,
        string $dimension,
        string $bucket,
        string $bucketKey,
        array $keyParts,
    ): int;
}
