<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final class Actor
{
    public function __construct(
        public string $type = 'user',
        public bool $required = true,
    ) {}
}
