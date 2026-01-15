<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Core;

use Illuminate\Support\Str;
use Tetthys\ActivityLog\Contracts\IdGenerator;

final class LaravelUlidGenerator implements IdGenerator
{
    public function generate(): string
    {
        return (string) Str::ulid();
    }
}
