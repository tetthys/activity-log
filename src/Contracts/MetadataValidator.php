<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

interface MetadataValidator
{
    /**
     * Validate metadata for an action. Must throw on validation failure.
     *
     * @param array<string, mixed> $metadata
     */
    public function validate(\UnitEnum $action, array $metadata): void;
}
