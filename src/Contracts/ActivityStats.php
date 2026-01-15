<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use Tetthys\ActivityLog\Model\ActorRef;
use Tetthys\ActivityLog\Model\ObjectReference;

interface ActivityStats
{
    public function countForActor(\UnitEnum $action, ActorRef $actor): int;

    /**
     * @return array<string, int> map of bucketKey => count
     */
    public function seriesForActor(
        \UnitEnum $action,
        ActorRef $actor,
        string $bucket,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): array;

    public function countForActorObject(\UnitEnum $action, ActorRef $actor, ObjectReference $object): int;
}
