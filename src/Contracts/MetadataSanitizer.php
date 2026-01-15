<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use Tetthys\ActivityLog\Enum\Sensitivity;

interface MetadataSanitizer
{
    /**
     * @param array<string,mixed> $metadata
     * @return array<string,mixed>
     */
    public function sanitize(array $metadata, Sensitivity $sensitivity): array;
}
