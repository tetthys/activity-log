<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\ActivityLogger;
use Tetthys\ActivityLog\Contracts\ActivityWriter;
use Tetthys\ActivityLog\Contracts\ActionDefinitionResolver;
use Tetthys\ActivityLog\Contracts\ActionNameResolver;
use Tetthys\ActivityLog\Contracts\Clock;
use Tetthys\ActivityLog\Contracts\IdGenerator;
use Tetthys\ActivityLog\Contracts\IdempotencyStore;
use Tetthys\ActivityLog\Contracts\MetadataSanitizer;
use Tetthys\ActivityLog\Contracts\MetadataValidator;
use Tetthys\ActivityLog\Model\ActivityEvent;
use Tetthys\ActivityLog\Model\ActivityRecord;
use Tetthys\ActivityLog\Model\ActorRef;
use Tetthys\ActivityLog\Model\ObjectReference;

final class DefaultActivityLogger implements ActivityLogger
{
    public function __construct(
        private readonly ActivityWriter $writer,
        private readonly ActionDefinitionResolver $definitions,
        private readonly ActionNameResolver $names,
        private readonly IdGenerator $ids,
        private readonly Clock $clock,
        private readonly MetadataValidator $validator,
        private readonly MetadataSanitizer $sanitizer,
        private readonly ?IdempotencyStore $idempotency = null,
    ) {}

    public function log(
        \UnitEnum $action,
        ?ActorRef $actor = null,
        ?ObjectReference $object = null,
        array $metadata = [],
        ?string $traceId = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?\DateTimeImmutable $occurredAt = null,
    ): ?ActivityRecord {
        $def = $this->definitions->resolve($action);

        // Validate actor/object requirements.
        if ($def->actorRequired && $actor === null) {
            throw new \InvalidArgumentException("Actor is required for action '{$def->name}'.");
        }
        if ($actor !== null && $def->actorType !== null && $actor->type !== $def->actorType) {
            throw new \InvalidArgumentException("Actor type mismatch for '{$def->name}': expected {$def->actorType}, got {$actor->type}.");
        }

        if ($def->objectRequired && $object === null) {
            throw new \InvalidArgumentException("Object is required for action '{$def->name}'.");
        }
        if ($object !== null && $def->objectType !== null && $object->type !== $def->objectType) {
            throw new \InvalidArgumentException("Object type mismatch for '{$def->name}': expected {$def->objectType}, got {$object->type}.");
        }

        // Validate metadata schema declared on the enum case.
        $this->validator->validate($action, $metadata);

        // Idempotency: optionally skip duplicates.
        if ($this->idempotency !== null && $def->uniqMode !== 'none') {
            $key = $this->buildIdempotencyKey($def->uniqMode, $def->name, $traceId, $actor, $object);
            $ttl = $def->uniqTtlSeconds;

            if ($key !== null) {
                $ok = $this->idempotency->registerOnce($key, $ttl);
                if (!$ok) {
                    return null;
                }
            }
        }

        $record = new ActivityRecord(
            id: $this->ids->generate(),
            action: $this->names->nameOf($action),
            occurredAt: $occurredAt ?? $this->clock->now(),
            actor: $actor,
            object: $object,
            ip: $ip,
            userAgent: $userAgent,
            traceId: $traceId,
            metadata: $this->sanitizer->sanitize($metadata),
        );

        $this->writer->write(new ActivityEvent($action, $record));

        return $record;
    }

    private function buildIdempotencyKey(
        string $mode,
        string $actionName,
        ?string $traceId,
        ?ActorRef $actor,
        ?ObjectReference $object,
    ): ?string {
        return match ($mode) {
            'trace' => $traceId ? "a:{$actionName}|t:{$traceId}" : null,

            'trace_actor_object' => $traceId
                ? "a:{$actionName}|t:{$traceId}"
                    . ($actor ? "|at:{$actor->type}|ai:{$actor->id}" : "|at:|ai:")
                    . ($object ? "|ot:{$object->type}|oi:{$object->id}" : "|ot:|oi:")
                : null,

            default => null,
        };
    }
}
