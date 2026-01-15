<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\IdempotencyStore;

final class InMemoryIdempotencyStore implements IdempotencyStore
{
    /** @var array<string, int> key => expiresAtEpoch */
    private array $seen = [];

    public function registerOnce(string $key, int $ttlSeconds = 0): bool
    {
        $now = time();

        // Cleanup opportunistically.
        foreach ($this->seen as $k => $exp) {
            if ($exp !== 0 && $exp < $now) {
                unset($this->seen[$k]);
            }
        }

        if (isset($this->seen[$key])) {
            $exp = $this->seen[$key];
            if ($exp === 0 || $exp >= $now) {
                return false;
            }
        }

        $expiresAt = $ttlSeconds > 0 ? ($now + $ttlSeconds) : 0;
        $this->seen[$key] = $expiresAt;

        return true;
    }
}
