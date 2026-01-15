<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Writers;

use Illuminate\Support\Facades\DB;
use Tetthys\ActivityLog\Contracts\ActivityWriter;
use Tetthys\ActivityLog\DTO\Activity;

final class DatabaseActivityWriter implements ActivityWriter
{
    public function __construct(
        private readonly string $table,
        private readonly bool $bestEffort = true,
    ) {}

    public function write(Activity $activity): void
    {
        $payload = $this->toRow($activity);

        try {
            DB::table($this->table)->insert($payload);
        } catch (\Throwable $e) {
            // Best-effort logging should never break the main request.
            if (!$this->bestEffort) {
                throw $e;
            }
        }
    }

    public function writeBatch(array $activities): void
    {
        $rows = [];
        foreach ($activities as $a) {
            $rows[] = $this->toRow($a);
        }

        if ($rows === []) {
            return;
        }

        try {
            DB::table($this->table)->insert($rows);
        } catch (\Throwable $e) {
            if (!$this->bestEffort) {
                throw $e;
            }
        }
    }

    /**
     * Convert Activity DTO into a DB row.
     *
     * @return array<string,mixed>
     */
    private function toRow(Activity $a): array
    {
        return [
            'id' => $a->id,
            'occurred_at' => $a->occurredAt->format('Y-m-d H:i:s'),

            'action' => $a->action,
            'auditable' => $a->auditable,
            'sensitivity' => $a->sensitivity->value,
            'category' => $a->category,
            'description' => $a->description,
            'retention_days' => $a->retentionDays,

            'actor_type' => $a->actor?->toArray()['type'] ?? null,
            'actor_id' => $a->actor?->toArray()['id'] ?? null,

            'subject_type' => $a->subject?->toArray()['type'] ?? null,
            'subject_id' => $a->subject?->toArray()['id'] ?? null,

            'correlation_id' => $a->correlationId,
            'ip' => $a->ip,
            'user_agent' => $a->userAgent,
            'channel' => $a->channel->value,

            'metadata' => $a->metadata === [] ? null : json_encode($a->metadata, JSON_UNESCAPED_UNICODE),

            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
