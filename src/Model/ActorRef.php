<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Model;

final readonly class ActorRef
{
    public function __construct(
        public string $type,
        public string $id,
    ) {}
}
