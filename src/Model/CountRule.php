<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Model;

final readonly class CountRule
{
    public function __construct(
        public string $dimension, // actor|object|action|actor_object
        public string $bucket,    // all|day|hour
    ) {}
}
