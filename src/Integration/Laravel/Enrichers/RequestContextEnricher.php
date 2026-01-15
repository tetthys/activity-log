<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Enrichers;

use Illuminate\Http\Request;
use Tetthys\ActivityLog\Contracts\ActivityEnricher;
use Tetthys\ActivityLog\DTO\Activity;
use Tetthys\ActivityLog\Enum\Channel;
use Tetthys\ActivityLog\Enum\Sensitivity;

final class RequestContextEnricher implements ActivityEnricher
{
    /**
     * @param string[] $correlationHeaders
     * @param string[] $apiPrefixes
     */
    public function __construct(
        private readonly Request $request,
        private readonly array $correlationHeaders = ['X-Correlation-Id', 'X-Request-Id'],
        private readonly array $apiPrefixes = ['/api'],
    ) {}

    public function enrich(Activity $activity): Activity
    {
        $ip = $activity->ip ?? $this->request->ip();
        $ua = $activity->userAgent ?? substr((string) $this->request->userAgent(), 0, 2048);

        $corr = $activity->correlationId ?? $this->resolveCorrelationId();

        $channel = $activity->channel;
        if ($channel === Channel::Unknown) {
            $channel = $this->inferChannel();
        }

        // Return a new Activity with injected request context
        return new Activity(
            id: $activity->id,
            occurredAt: $activity->occurredAt,

            action: $activity->action,
            auditable: $activity->auditable,
            sensitivity: $activity->sensitivity ?? Sensitivity::Normal,
            category: $activity->category,
            description: $activity->description,
            retentionDays: $activity->retentionDays,

            actor: $activity->actor,
            subject: $activity->subject,
            metadata: $activity->metadata,

            correlationId: $corr,
            ip: $ip,
            userAgent: $ua,
            channel: $channel,
        );
    }

    private function resolveCorrelationId(): ?string
    {
        foreach ($this->correlationHeaders as $h) {
            $v = $this->request->header($h);
            if (is_string($v) && trim($v) !== '') {
                return trim($v);
            }
        }

        return null;
    }

    private function inferChannel(): Channel
    {
        $path = '/' . ltrim($this->request->path(), '/');

        foreach ($this->apiPrefixes as $prefix) {
            $p = '/' . ltrim($prefix, '/');
            if (str_starts_with($path, $p)) {
                return Channel::Api;
            }
        }

        return Channel::Web;
    }
}
