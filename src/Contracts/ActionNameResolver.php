<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

use BackedEnum;

interface ActionNameResolver
{
    public function resolve(BackedEnum|string $action): string;
}
