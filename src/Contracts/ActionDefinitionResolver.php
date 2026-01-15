<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use BackedEnum;
use Tetthys\ActivityLog\DTO\ActionDefinition;

interface ActionDefinitionResolver
{
    public function resolve(BackedEnum|string $action): ActionDefinition;
}
