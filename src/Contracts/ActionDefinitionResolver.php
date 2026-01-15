<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use Tetthys\ActivityLog\Model\ActionDefinition;

interface ActionDefinitionResolver
{
    public function resolve(\UnitEnum $action): ActionDefinition;
}
