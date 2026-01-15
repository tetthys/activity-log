<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use Tetthys\ActivityLog\DTO\Activity;

interface ActivityEnricher
{
    public function enrich(Activity $activity): Activity;
}
