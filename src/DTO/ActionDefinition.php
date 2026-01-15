<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\DTO;

use Tetthys\ActivityLog\Enum\Sensitivity;

final class ActionDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly bool $auditable = true,
        public readonly Sensitivity $sensitivity = Sensitivity::Normal,
        public readonly ?string $description = null,
        public readonly ?string $category = null,
        public readonly ?int $retentionDays = null,
    ) {}
}
