<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\ActivityWriter;
use Tetthys\ActivityLog\Model\ActivityEvent;

final class InMemoryActivityWriter implements ActivityWriter
{
    /** @var list<ActivityEvent> */
    private array $events = [];

    public function write(ActivityEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return list<ActivityEvent>
     */
    public function all(): array
    {
        return $this->events;
    }
}
