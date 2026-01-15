<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use Tetthys\ActivityLog\Model\CountPlan;

interface CountPlanResolver
{
    public function resolve(\UnitEnum $action): CountPlan;
}
