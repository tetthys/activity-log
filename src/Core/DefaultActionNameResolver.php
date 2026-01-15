<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\ActionNameResolver;
use Tetthys\ActivityLog\Attributes\ActivityAction;

final class DefaultActionNameResolver implements ActionNameResolver
{
    public function nameOf(\UnitEnum $action): string
    {
        $ref = new \ReflectionEnumUnitCase($action::class, $action->name);
        $attrs = $ref->getAttributes(ActivityAction::class);

        if ($attrs === []) {
            // Fallback: stable but not pretty.
            return strtolower(str_replace('\\', '.', $action::class) . '.' . strtolower($action->name));
        }

        /** @var ActivityAction $def */
        $def = $attrs[0]->newInstance();
        return $def->name;
    }
}
