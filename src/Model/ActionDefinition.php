<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Model;

final readonly class ActionDefinition
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?string $category,
        public int $version,
        public ?string $actorType,
        public bool $actorRequired,
        public ?string $objectType,
        public bool $objectRequired,
        public string $uniqMode,
        public int $uniqTtlSeconds,
    ) {}
}
