<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use BackedEnum;
use Tetthys\ActivityLog\DTO\ActorRef;
use Tetthys\ActivityLog\DTO\SubjectRef;
use Tetthys\ActivityLog\Enum\Channel;

interface ActivityLogger
{
    /**
     * @param BackedEnum|string $action
     * @param array<string,mixed> $metadata
     */
    public function record(
        BackedEnum|string $action,
        ?ActorRef $actor = null,
        ?SubjectRef $subject = null,
        array $metadata = [],
        ?string $correlationId = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?Channel $channel = null,
    ): void;
}
