<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Core;

use Illuminate\Support\Carbon;
use Tetthys\ActivityLog\Contracts\Clock;

final class LaravelClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        // Use Carbon for app timezone configuration, then convert to immutable.
        return Carbon::now()->toImmutable();
    }
}
