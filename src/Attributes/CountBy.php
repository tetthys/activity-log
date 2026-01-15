<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
final class CountBy
{
    public function __construct(
        public string $dimension, // actor|object|action|actor_object
        public string $bucket = 'all', // all|day|hour
    ) {}
}
