<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use BackedEnum;
use Tetthys\ActivityLog\Contracts\ActionNameResolver;

final class DefaultActionNameResolver implements ActionNameResolver
{
    public function resolve(BackedEnum|string $action): string
    {
        return $action instanceof BackedEnum
            ? (string) $action->value
            : trim((string) $action);
    }
}
