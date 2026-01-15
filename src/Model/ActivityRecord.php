<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Model;

final readonly class ActivityRecord
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $id,
        public string $action, // resolved action name
        public \DateTimeImmutable $occurredAt,
        public ?ActorRef $actor = null,
        public ?ObjectReference $object = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $traceId = null,
        public array $metadata = [],
    ) {}
}
