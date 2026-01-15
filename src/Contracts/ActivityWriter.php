<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use Tetthys\ActivityLog\Model\ActivityEvent;

interface ActivityWriter
{
    public function write(ActivityEvent $event): void;
}
