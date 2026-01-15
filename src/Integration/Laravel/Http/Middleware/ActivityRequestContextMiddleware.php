<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tetthys\ActivityLog\Integration\Laravel\Http\RequestContext;
use Tetthys\ActivityLog\Integration\Laravel\Http\RequestContextStore;

final class ActivityRequestContextMiddleware
{
    public function __construct(
        private readonly RequestContextStore $store,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Capture minimal context that is useful for auditing & idempotency.
        $traceId = $request->headers->get('X-Request-Id')
            ?? $request->headers->get('X-Correlation-Id')
            ?? null;

        $this->store->set(new RequestContext(
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            traceId: $traceId,
        ));

        return $next($request);
    }
}
