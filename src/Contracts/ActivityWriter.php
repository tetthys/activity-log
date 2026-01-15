<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use Tetthys\ActivityLog\DTO\Activity;

interface ActivityWriter
{
    public function write(Activity $activity): void;

    /**
     * @param Activity[] $activities
     */
    public function writeBatch(array $activities): void;
}
