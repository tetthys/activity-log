<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

interface MetadataSanitizer
{
    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    public function sanitize(array $metadata): array;
}
