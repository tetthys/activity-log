<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Model;

final readonly class ActivityEvent
{
    public function __construct(
        public \UnitEnum $actionEnum,
        public ActivityRecord $record,
    ) {}
}
