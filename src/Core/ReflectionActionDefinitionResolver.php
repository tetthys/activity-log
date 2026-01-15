<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Attributes\ActivityAction;
use Tetthys\ActivityLog\Attributes\Actor;
use Tetthys\ActivityLog\Attributes\ObjectRef;
use Tetthys\ActivityLog\Attributes\Uniq;
use Tetthys\ActivityLog\Contracts\ActionDefinitionResolver;
use Tetthys\ActivityLog\Model\ActionDefinition;

final class ReflectionActionDefinitionResolver implements ActionDefinitionResolver
{
    /** @var array<string, ActionDefinition> */
    private array $cache = [];

    public function resolve(\UnitEnum $action): ActionDefinition
    {
        $key = $action::class . '::' . $action->name;
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $ref = new \ReflectionEnumUnitCase($action::class, $action->name);

        $actionAttr = $ref->getAttributes(ActivityAction::class)[0] ?? null;
        if ($actionAttr === null) {
            throw new \LogicException("Missing #[ActivityAction] on {$key}");
        }
        /** @var ActivityAction $a */
        $a = $actionAttr->newInstance();

        $actorAttr = $ref->getAttributes(Actor::class)[0] ?? null;
        $actorType = null;
        $actorRequired = false;
        if ($actorAttr !== null) {
            /** @var Actor $act */
            $act = $actorAttr->newInstance();
            $actorType = $act->type;
            $actorRequired = $act->required;
        }

        $objectAttr = $ref->getAttributes(ObjectRef::class)[0] ?? null;
        $objectType = null;
        $objectRequired = false;
        if ($objectAttr !== null) {
            /** @var ObjectRef $obj */
            $obj = $objectAttr->newInstance();
            $objectType = $obj->type;
            $objectRequired = $obj->required;
        }

        $uniqAttr = $ref->getAttributes(Uniq::class)[0] ?? null;
        $uniqMode = 'none';
        $uniqTtl = 0;
        if ($uniqAttr !== null) {
            /** @var Uniq $u */
            $u = $uniqAttr->newInstance();
            $uniqMode = $u->mode;
            $uniqTtl = $u->ttlSeconds;
        }

        return $this->cache[$key] = new ActionDefinition(
            name: $a->name,
            description: $a->description,
            category: $a->category,
            version: $a->version,
            actorType: $actorType,
            actorRequired: $actorRequired,
            objectType: $objectType,
            objectRequired: $objectRequired,
            uniqMode: $uniqMode,
            uniqTtlSeconds: $uniqTtl,
        );
    }
}
