<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Tetthys\ActivityLog\Contracts\ActionNameResolver;
use Tetthys\ActivityLog\Contracts\ActionDefinitionResolver;
use Tetthys\ActivityLog\Contracts\ActivityLogger;
use Tetthys\ActivityLog\Contracts\ActivityWriter;
use Tetthys\ActivityLog\Contracts\Clock;
use Tetthys\ActivityLog\Contracts\IdGenerator;
use Tetthys\ActivityLog\Contracts\MetadataSanitizer;
use Tetthys\ActivityLog\Core\DefaultActionNameResolver;
use Tetthys\ActivityLog\Core\DefaultActivityLogger;
use Tetthys\ActivityLog\Core\ReflectionActionDefinitionResolver;
use Tetthys\ActivityLog\Core\SystemClock;
use Tetthys\ActivityLog\Core\UlidGenerator;
use Tetthys\ActivityLog\Core\FailSoftMetadataSanitizer;
use Tetthys\ActivityLog\Integration\Laravel\Enrichers\RequestContextEnricher;
use Tetthys\ActivityLog\Integration\Laravel\Writers\DatabaseActivityWriter;

final class LaravelActivityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'activity-log');

        $this->app->singleton(ActionNameResolver::class, fn () => new DefaultActionNameResolver());

        $this->app->singleton(ActionDefinitionResolver::class, function (Application $app) {
            return new ReflectionActionDefinitionResolver(
                names: $app->make(ActionNameResolver::class),
            );
        });

        $this->app->singleton(Clock::class, fn () => new SystemClock());
        $this->app->singleton(IdGenerator::class, fn () => new UlidGenerator());

        $this->app->singleton(MetadataSanitizer::class, function (Application $app) {
            $masked = (array) config('activity-log.masked_keys', []);
            return new FailSoftMetadataSanitizer($masked);
        });

        $this->app->singleton(ActivityWriter::class, function () {
            return new DatabaseActivityWriter(
                table: (string) config('activity-log.table', 'activity_logs'),
                bestEffort: (bool) config('activity-log.best_effort', true),
            );
        });

        $this->app->singleton(ActivityLogger::class, function (Application $app) {
            // RequestContextEnricher is per-request safe in Laravel (Request is scoped).
            $enrichers = [
                new RequestContextEnricher(
                    request: $app['request'],
                    correlationHeaders: (array) config('activity-log.correlation_headers', []),
                    apiPrefixes: (array) config('activity-log.channel.api_prefixes', ['/api']),
                ),
            ];

            return new DefaultActivityLogger(
                writer: $app->make(ActivityWriter::class),
                clock: $app->make(Clock::class),
                ids: $app->make(IdGenerator::class),
                definitions: $app->make(ActionDefinitionResolver::class),
                metadata: $app->make(MetadataSanitizer::class),
                enrichers: $enrichers,
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            $this->configPath() => config_path('activity-log.php'),
        ], 'activity-log-config');

        // Load migrations from package
        $this->loadMigrationsFrom($this->migrationsPath());
    }

    private function configPath(): string
    {
        return __DIR__ . '/config/activity-log.php';
    }

    private function migrationsPath(): string
    {
        return __DIR__ . '/database/migrations';
    }
}
