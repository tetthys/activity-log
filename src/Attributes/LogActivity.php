<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Attributes;

use Attribute;
use BackedEnum;

/**
 * Declarative method-level logging marker.
 * Interpretation is adapter-specific.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class LogActivity
{
    public function __construct(
        public readonly BackedEnum|string $action,
        public readonly ?string $subjectParam = null,
        public readonly BackedEnum|string|null $subjectType = null,
        public readonly BackedEnum|string|null $actorType = null,
    ) {}
}
