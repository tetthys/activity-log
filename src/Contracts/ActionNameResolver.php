<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

interface ActionNameResolver
{
    public function nameOf(\UnitEnum $action): string;
}
