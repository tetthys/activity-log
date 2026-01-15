<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final class ActivityAction
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $category = null,
        public int $version = 1,
    ) {}
}
