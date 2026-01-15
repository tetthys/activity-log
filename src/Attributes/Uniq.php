<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final class Uniq
{
    public function __construct(
        public string $mode = 'none', // none|trace|trace_actor_object
        public int $ttlSeconds = 0,   // 0 means no TTL (store decides)
    ) {}
}
