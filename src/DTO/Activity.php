<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\DTO;

use Tetthys\ActivityLog\Enum\Channel;

/**
 * Immutable activity event.
 */
final class Activity
{
    /**
     * @param array<string,mixed> $metadata
     */
    public function __construct(
        public readonly string $id,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly string $action,
        public readonly ?ActorRef $actor = null,
        public readonly ?SubjectRef $subject = null,
        public readonly array $metadata = [],
        public readonly ?string $correlationId = null,
        public readonly ?string $ip = null,
        public readonly ?string $userAgent = null,
        public readonly Channel $channel = Channel::Unknown,
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'occurred_at' => $this->occurredAt->format('c'),
            'action' => $this->action,
            'actor' => $this->actor?->toArray(),
            'subject' => $this->subject?->toArray(),
            'metadata' => $this->metadata,
            'correlation_id' => $this->correlationId,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'channel' => $this->channel->value,
        ];
    }
}
