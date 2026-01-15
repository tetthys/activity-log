<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Http;

use Tetthys\ActivityLog\Contracts\ActivityLogger;
use Tetthys\ActivityLog\Model\ActorRef;
use Tetthys\ActivityLog\Model\ObjectReference;
use Tetthys\ActivityLog\Model\ActivityRecord;

final class ContextualActivityLogger
{
    public function __construct(
        private readonly ActivityLogger $inner,
        private readonly RequestContextStore $store,
    ) {}

    /**
     * Log using request-scoped context (ip, userAgent, traceId) if available.
     *
     * @param array<string, mixed> $metadata
     */
    public function log(
        \UnitEnum $action,
        ?ActorRef $actor = null,
        ?ObjectReference $object = null,
        array $metadata = [],
        ?\DateTimeImmutable $occurredAt = null,
    ): ?ActivityRecord {
        $ctx = $this->store->get();

        return $this->inner->log(
            action: $action,
            actor: $actor,
            object: $object,
            metadata: $metadata,
            traceId: $ctx?->traceId,
            ip: $ctx?->ip,
            userAgent: $ctx?->userAgent,
            occurredAt: $occurredAt,
        );
    }
}
