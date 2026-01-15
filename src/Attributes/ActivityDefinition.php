<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Attributes;

use Attribute;
use Tetthys\ActivityLog\Enum\Sensitivity;

/**
 * Metadata for user-defined Action enums.
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final class ActivityDefinition
{
    public function __construct(
        public readonly bool $auditable = true,
        public readonly Sensitivity $sensitivity = Sensitivity::Normal,
        public readonly ?string $description = null,
        public readonly ?string $category = null,
        public readonly ?int $retentionDays = null,
    ) {}
}
