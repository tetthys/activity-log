<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\CounterStore;

final class InMemoryCounterStore implements CounterStore
{
    /** @var array<string, int> */
    private array $counts = [];

    public function increment(
        string $action,
        string $dimension,
        string $bucket,
        string $bucketKey,
        array $keyParts,
        int $by = 1,
    ): void {
        $k = $this->makeKey($action, $dimension, $bucket, $bucketKey, $keyParts);
        $this->counts[$k] = ($this->counts[$k] ?? 0) + $by;
    }

    public function get(
        string $action,
        string $dimension,
        string $bucket,
        string $bucketKey,
        array $keyParts,
    ): int {
        $k = $this->makeKey($action, $dimension, $bucket, $bucketKey, $keyParts);
        return $this->counts[$k] ?? 0;
    }

    private function makeKey(string $action, string $dimension, string $bucket, string $bucketKey, array $keyParts): string
    {
        return implode('|', [
            $action,
            $dimension,
            $bucket,
            $bucketKey,
            (string) ($keyParts['actor_type'] ?? ''),
            (string) ($keyParts['actor_id'] ?? ''),
            (string) ($keyParts['object_type'] ?? ''),
            (string) ($keyParts['object_id'] ?? ''),
        ]);
    }
}
