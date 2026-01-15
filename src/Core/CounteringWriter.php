<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\ActivityWriter;
use Tetthys\ActivityLog\Contracts\CountPlanResolver;
use Tetthys\ActivityLog\Contracts\CounterStore;
use Tetthys\ActivityLog\Model\ActivityEvent;

final class CounteringWriter implements ActivityWriter
{
    public function __construct(
        private readonly ActivityWriter $inner,
        private readonly CountPlanResolver $plans,
        private readonly CounterStore $counters,
    ) {}

    public function write(ActivityEvent $event): void
    {
        // Write raw activity first.
        $this->inner->write($event);

        // Then update counters for fast reads.
        $plan = $this->plans->resolve($event->actionEnum);

        if ($plan->rules === []) {
            return;
        }

        $record = $event->record;

        foreach ($plan->rules as $rule) {
            $bucketKey = $this->bucketKey($rule->bucket, $record->occurredAt);

            $keyParts = [
                'actor_type' => $record->actor?->type,
                'actor_id' => $record->actor?->id,
                'object_type' => $record->object?->type,
                'object_id' => $record->object?->id,
            ];

            // Normalize by dimension.
            $normalized = match ($rule->dimension) {
                'action' => ['actor_type' => null, 'actor_id' => null, 'object_type' => null, 'object_id' => null],
                'actor' => ['actor_type' => $keyParts['actor_type'], 'actor_id' => $keyParts['actor_id'], 'object_type' => null, 'object_id' => null],
                'object' => ['actor_type' => null, 'actor_id' => null, 'object_type' => $keyParts['object_type'], 'object_id' => $keyParts['object_id']],
                'actor_object' => $keyParts,
                default => $keyParts,
            };

            $this->counters->increment(
                action: $record->action,
                dimension: $rule->dimension,
                bucket: $rule->bucket,
                bucketKey: $bucketKey,
                keyParts: $normalized,
                by: 1,
            );
        }
    }

    private function bucketKey(string $bucket, \DateTimeImmutable $dt): string
    {
        return match ($bucket) {
            'day' => $dt->format('Y-m-d'),
            'hour' => $dt->format('Y-m-d\TH:00'),
            default => 'all',
        };
    }
}
