<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use BackedEnum;
use Tetthys\ActivityLog\Contracts\ActivityEnricher;
use Tetthys\ActivityLog\Contracts\ActivityLogger;
use Tetthys\ActivityLog\Contracts\ActivityWriter;
use Tetthys\ActivityLog\Contracts\ActionNameResolver;
use Tetthys\ActivityLog\Contracts\Clock;
use Tetthys\ActivityLog\Contracts\IdGenerator;
use Tetthys\ActivityLog\Contracts\MetadataSanitizer;
use Tetthys\ActivityLog\DTO\Activity;
use Tetthys\ActivityLog\DTO\ActorRef;
use Tetthys\ActivityLog\DTO\SubjectRef;
use Tetthys\ActivityLog\Enum\Channel;

final class DefaultActivityLogger implements ActivityLogger
{
    /** @var ActivityEnricher[] */
    private array $enrichers;

    public function __construct(
        private readonly ActivityWriter $writer,
        private readonly Clock $clock,
        private readonly IdGenerator $ids,
        private readonly ActionNameResolver $actions,
        private readonly MetadataSanitizer $metadata,
        array $enrichers = [],
    ) {
        $this->enrichers = $enrichers;
    }

    public function record(
        BackedEnum|string $action,
        ?ActorRef $actor = null,
        ?SubjectRef $subject = null,
        array $metadata = [],
        ?string $correlationId = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?Channel $channel = null,
    ): void {
        $activity = new Activity(
            id: $this->ids->generate(),
            occurredAt: $this->clock->now(),
            action: $this->actions->resolve($action),
            actor: $actor,
            subject: $subject,
            metadata: $this->metadata->sanitize($metadata),
            correlationId: $correlationId,
            ip: $ip,
            userAgent: $userAgent,
            channel: $channel ?? Channel::Unknown,
        );

        foreach ($this->enrichers as $enricher) {
            $activity = $enricher->enrich($activity);
        }

        $this->writer->write($activity);
    }
}
