<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Attributes\CountBy;
use Tetthys\ActivityLog\Contracts\CountPlanResolver;
use Tetthys\ActivityLog\Model\CountPlan;
use Tetthys\ActivityLog\Model\CountRule;

final class ReflectionCountPlanResolver implements CountPlanResolver
{
    /** @var array<string, CountPlan> */
    private array $cache = [];

    public function resolve(\UnitEnum $action): CountPlan
    {
        $key = $action::class . '::' . $action->name;
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $ref = new \ReflectionEnumUnitCase($action::class, $action->name);
        $attrs = $ref->getAttributes(CountBy::class);

        $rules = [];
        foreach ($attrs as $attr) {
            /** @var CountBy $c */
            $c = $attr->newInstance();
            $rules[] = new CountRule($c->dimension, $c->bucket);
        }

        return $this->cache[$key] = new CountPlan($rules);
    }
}
