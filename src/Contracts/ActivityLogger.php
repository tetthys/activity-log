<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use Tetthys\ActivityLog\Model\ActorRef;
use Tetthys\ActivityLog\Model\ObjectReference;
use Tetthys\ActivityLog\Model\ActivityRecord;

interface ActivityLogger
{
    /**
     * Returns ActivityRecord if written, null if skipped by idempotency policy.
     *
     * @param array<string, mixed> $metadata
     */
    public function log(
        \UnitEnum $action,
        ?ActorRef $actor = null,
        ?ObjectReference $object = null,
        array $metadata = [],
        ?string $traceId = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?\DateTimeImmutable $occurredAt = null,
    ): ?ActivityRecord;
}
