<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Tetthys\ActivityLog\Contracts\ActivityLogger;
use Tetthys\ActivityLog\Contracts\ActivityStats;
use Tetthys\ActivityLog\Contracts\ActionDefinitionResolver;
use Tetthys\ActivityLog\Contracts\ActionNameResolver;
use Tetthys\ActivityLog\Contracts\Clock;
use Tetthys\ActivityLog\Contracts\CountPlanResolver;
use Tetthys\ActivityLog\Contracts\CounterStore;
use Tetthys\ActivityLog\Contracts\IdGenerator;
use Tetthys\ActivityLog\Contracts\IdempotencyStore;
use Tetthys\ActivityLog\Contracts\MetadataSanitizer;
use Tetthys\ActivityLog\Contracts\MetadataValidator;
use Tetthys\ActivityLog\Core\AttributeMetadataValidator;
use Tetthys\ActivityLog\Core\CounterBackedStats;
use Tetthys\ActivityLog\Core\CounteringWriter;
use Tetthys\ActivityLog\Core\DefaultActionNameResolver;
use Tetthys\ActivityLog\Core\DefaultActivityLogger;
use Tetthys\ActivityLog\Core\FailSoftMetadataSanitizer;
use Tetthys\ActivityLog\Core\ReflectionActionDefinitionResolver;
use Tetthys\ActivityLog\Core\ReflectionCountPlanResolver;
use Tetthys\ActivityLog\Integration\Laravel\Core\LaravelClock;
use Tetthys\ActivityLog\Integration\Laravel\Core\LaravelUlidGenerator;
use Tetthys\ActivityLog\Integration\Laravel\Counters\DatabaseCounterStore;
use Tetthys\ActivityLog\Integration\Laravel\Idempotency\CacheIdempotencyStore;
use Tetthys\ActivityLog\Integration\Laravel\Writers\DatabaseActivityWriter;

final class LaravelActivityLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Config
        $this->mergeConfigFrom(__DIR__ . '/config/activity-log.php', 'activity-log');

        // Core bindings
        $this->app->singleton(ActionNameResolver::class, DefaultActionNameResolver::class);
        $this->app->singleton(ActionDefinitionResolver::class, ReflectionActionDefinitionResolver::class);
        $this->app->singleton(CountPlanResolver::class, ReflectionCountPlanResolver::class);

        $this->app->singleton(MetadataValidator::class, AttributeMetadataValidator::class);
        $this->app->singleton(MetadataSanitizer::class, function () {
            /** @var array<string> $blocked */
            $blocked = (array) config('activity-log.metadata.blocked_keys', []);
            return new FailSoftMetadataSanitizer(blockedKeys: $blocked);
        });

        $this->app->singleton(Clock::class, LaravelClock::class);
        $this->app->singleton(IdGenerator::class, LaravelUlidGenerator::class);

        // Stores / writers
        $this->app->singleton(CounterStore::class, function (Application $app) {
            return new DatabaseCounterStore(
                connection: $app['db'],
                table: (string) config('activity-log.tables.counters', 'activity_counters'),
            );
        });

        $this->app->singleton(IdempotencyStore::class, function (Application $app) {
            /** @var CacheRepository $cache */
            $cache = $app->make(CacheRepository::class);
            return new CacheIdempotencyStore(
                cache: $cache,
                prefix: (string) config('activity-log.idempotency.prefix', 'activity:idem:'),
                defaultTtlSeconds: (int) config('activity-log.idempotency.default_ttl_seconds', 300),
            );
        });

        $this->app->singleton(DatabaseActivityWriter::class, function (Application $app) {
            return new DatabaseActivityWriter(
                connection: $app['db'],
                table: (string) config('activity-log.tables.logs', 'activity_logs'),
            );
        });

        // The ActivityWriter in Laravel is the countering decorator around the DB writer.
        $this->app->singleton(\Tetthys\ActivityLog\Contracts\ActivityWriter::class, function (Application $app) {
            return new CounteringWriter(
                inner: $app->make(DatabaseActivityWriter::class),
                plans: $app->make(CountPlanResolver::class),
                counters: $app->make(CounterStore::class),
            );
        });

        // Logger
        $this->app->singleton(ActivityLogger::class, function (Application $app) {
            return new DefaultActivityLogger(
                writer: $app->make(\Tetthys\ActivityLog\Contracts\ActivityWriter::class),
                definitions: $app->make(ActionDefinitionResolver::class),
                names: $app->make(ActionNameResolver::class),
                ids: $app->make(IdGenerator::class),
                clock: $app->make(Clock::class),
                validator: $app->make(MetadataValidator::class),
                sanitizer: $app->make(MetadataSanitizer::class),
                idempotency: $app->make(IdempotencyStore::class),
            );
        });

        // Stats (counter-backed)
        $this->app->singleton(ActivityStats::class, function (Application $app) {
            return new CounterBackedStats(
                counters: $app->make(CounterStore::class),
                names: $app->make(ActionNameResolver::class),
            );
        });
    }

    public function boot(): void
    {
        // Publish config and migrations stubs.
        $this->publishes([
            __DIR__ . '/config/activity-log.php' => config_path('activity-log.php'),
        ], 'activity-log-config');

        $this->publishes([
            __DIR__ . '/stubs/create_activity_log_tables.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_create_activity_log_tables.php'),
        ], 'activity-log-migrations');
    }
}
