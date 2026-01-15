<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\ActivityStats;
use Tetthys\ActivityLog\Contracts\ActionNameResolver;
use Tetthys\ActivityLog\Contracts\CounterStore;
use Tetthys\ActivityLog\Model\ActorRef;
use Tetthys\ActivityLog\Model\ObjectReference;

final class CounterBackedStats implements ActivityStats
{
    public function __construct(
        private readonly CounterStore $counters,
        private readonly ActionNameResolver $names,
    ) {}

    public function countForActor(\UnitEnum $action, ActorRef $actor): int
    {
        return $this->counters->get(
            action: $this->names->nameOf($action),
            dimension: 'actor',
            bucket: 'all',
            bucketKey: 'all',
            keyParts: [
                'actor_type' => $actor->type,
                'actor_id' => $actor->id,
                'object_type' => null,
                'object_id' => null,
            ],
        );
    }

    public function countForActorObject(\UnitEnum $action, ActorRef $actor, ObjectReference $object): int
    {
        return $this->counters->get(
            action: $this->names->nameOf($action),
            dimension: 'actor_object',
            bucket: 'all',
            bucketKey: 'all',
            keyParts: [
                'actor_type' => $actor->type,
                'actor_id' => $actor->id,
                'object_type' => $object->type,
                'object_id' => $object->id,
            ],
        );
    }

    public function seriesForActor(
        \UnitEnum $action,
        ActorRef $actor,
        string $bucket,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): array {
        if (!in_array($bucket, ['day', 'hour'], true)) {
            throw new \InvalidArgumentException("Unsupported bucket '{$bucket}'.");
        }

        $out = [];
        $cursor = $from;

        while ($cursor <= $to) {
            $bucketKey = $bucket === 'day'
                ? $cursor->format('Y-m-d')
                : $cursor->format('Y-m-d\TH:00');

            $out[$bucketKey] = $this->counters->get(
                action: $this->names->nameOf($action),
                dimension: 'actor',
                bucket: $bucket,
                bucketKey: $bucketKey,
                keyParts: [
                    'actor_type' => $actor->type,
                    'actor_id' => $actor->id,
                    'object_type' => null,
                    'object_id' => null,
                ],
            );

            $cursor = $bucket === 'day'
                ? $cursor->modify('+1 day')
                : $cursor->modify('+1 hour');
        }

        return $out;
    }
}
