<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Writers;

use Illuminate\Database\DatabaseManager;
use Tetthys\ActivityLog\Contracts\ActivityWriter;
use Tetthys\ActivityLog\Model\ActivityEvent;

final class DatabaseActivityWriter implements ActivityWriter
{
    public function __construct(
        private readonly DatabaseManager $connection,
        private readonly string $table = 'activity_logs',
    ) {}

    public function write(ActivityEvent $event): void
    {
        $r = $event->record;

        $this->connection->table($this->table)->insert([
            'id' => $r->id,
            'action' => $r->action,
            'occurred_at' => $r->occurredAt,
            'actor_type' => $r->actor?->type,
            'actor_id' => $r->actor?->id,
            'object_type' => $r->object?->type,
            'object_id' => $r->object?->id,
            'ip' => $r->ip,
            'user_agent' => $r->userAgent,
            'trace_id' => $r->traceId,
            'metadata' => $r->metadata === [] ? null : json_encode($r->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
