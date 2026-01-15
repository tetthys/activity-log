<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Counters;

use Illuminate\Database\DatabaseManager;

final class DatabaseCounterStore implements \Tetthys\ActivityLog\Contracts\CounterStore
{
    public function __construct(
        private readonly DatabaseManager $connection,
        private readonly string $table = 'activity_counters',
    ) {}

    public function increment(
        string $action,
        string $dimension,
        string $bucket,
        string $bucketKey,
        array $keyParts,
        int $by = 1,
    ): void {
        $now = now();

        $row = [
            'action' => $action,
            'dimension' => $dimension,
            'bucket' => $bucket,
            'bucket_key' => $bucketKey,
            'actor_type' => $keyParts['actor_type'] ?? null,
            'actor_id' => $keyParts['actor_id'] ?? null,
            'object_type' => $keyParts['object_type'] ?? null,
            'object_id' => $keyParts['object_id'] ?? null,
            'count' => $by,
            'updated_at' => $now,
            'created_at' => $now,
        ];

        // Use driver-specific atomic upsert for correctness under concurrency.
        $driver = $this->connection->connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $this->upsertMySql($row);
            return;
        }

        if ($driver === 'pgsql') {
            $this->upsertPostgres($row);
            return;
        }

        // Fallback: best-effort using query builder upsert (may not be atomic increment on all drivers).
        $this->connection->table($this->table)->upsert(
            [$row],
            ['action', 'dimension', 'bucket', 'bucket_key', 'actor_type', 'actor_id', 'object_type', 'object_id'],
            ['count', 'updated_at']
        );
    }

    public function get(
        string $action,
        string $dimension,
        string $bucket,
        string $bucketKey,
        array $keyParts,
    ): int {
        $q = $this->connection->table($this->table)
            ->where('action', $action)
            ->where('dimension', $dimension)
            ->where('bucket', $bucket)
            ->where('bucket_key', $bucketKey)
            ->where('actor_type', $keyParts['actor_type'] ?? null)
            ->where('actor_id', $keyParts['actor_id'] ?? null)
            ->where('object_type', $keyParts['object_type'] ?? null)
            ->where('object_id', $keyParts['object_id'] ?? null)
            ->value('count');

        return (int) ($q ?? 0);
    }

    private function upsertMySql(array $row): void
    {
        $sql = "INSERT INTO {$this->table} (action, dimension, bucket, bucket_key, actor_type, actor_id, object_type, object_id, count, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    count = count + VALUES(count),
                    updated_at = VALUES(updated_at)";

        $this->connection->statement($sql, [
            $row['action'],
            $row['dimension'],
            $row['bucket'],
            $row['bucket_key'],
            $row['actor_type'],
            $row['actor_id'],
            $row['object_type'],
            $row['object_id'],
            $row['count'],
            $row['created_at'],
            $row['updated_at'],
        ]);
    }

    private function upsertPostgres(array $row): void
    {
        $sql = "INSERT INTO {$this->table} (action, dimension, bucket, bucket_key, actor_type, actor_id, object_type, object_id, count, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT (action, dimension, bucket, bucket_key, actor_type, actor_id, object_type, object_id)
                DO UPDATE SET
                    count = {$this->table}.count + EXCLUDED.count,
                    updated_at = EXCLUDED.updated_at";

        $this->connection->statement($sql, [
            $row['action'],
            $row['dimension'],
            $row['bucket'],
            $row['bucket_key'],
            $row['actor_type'],
            $row['actor_id'],
            $row['object_type'],
            $row['object_id'],
            $row['count'],
            $row['created_at'],
            $row['updated_at'],
        ]);
    }
}
