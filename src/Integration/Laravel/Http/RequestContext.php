<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Http;

final readonly class RequestContext
{
    public function __construct(
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $traceId = null,
    ) {}
}
