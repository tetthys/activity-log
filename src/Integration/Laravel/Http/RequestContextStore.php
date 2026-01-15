<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Integration\Laravel\Http;

final class RequestContextStore
{
    private ?RequestContext $ctx = null;

    public function set(RequestContext $ctx): void
    {
        $this->ctx = $ctx;
    }

    public function get(): ?RequestContext
    {
        return $this->ctx;
    }
}
