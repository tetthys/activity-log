<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use BackedEnum;
use ReflectionEnumUnitCase;
use Tetthys\ActivityLog\Attributes\ActivityDefinition as ActivityDefinitionAttribute;
use Tetthys\ActivityLog\Contracts\ActionDefinitionResolver;
use Tetthys\ActivityLog\Contracts\ActionNameResolver;
use Tetthys\ActivityLog\DTO\ActionDefinition;
use Tetthys\ActivityLog\Enum\Sensitivity;

final class ReflectionActionDefinitionResolver implements ActionDefinitionResolver
{
    /** @var array<string, ActionDefinition> */
    private array $cache = [];

    public function __construct(
        private readonly ActionNameResolver $names,
    ) {}

    public function resolve(BackedEnum|string $action): ActionDefinition
    {
        $name = $this->names->resolve($action);

        // Cache by resolved action name (stable key)
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        // Default definition (used for string actions or enum cases without attribute)
        $def = new ActionDefinition(
            name: $name,
            auditable: true,
            sensitivity: Sensitivity::Normal,
            description: null,
            category: null,
            retentionDays: null,
        );

        if ($action instanceof BackedEnum) {
            $ref = new ReflectionEnumUnitCase($action::class, $action->name);

            $attrs = $ref->getAttributes(ActivityDefinitionAttribute::class);
            if ($attrs !== []) {
                /** @var ActivityDefinitionAttribute $meta */
                $meta = $attrs[0]->newInstance();

                $def = new ActionDefinition(
                    name: $name,
                    auditable: $meta->auditable,
                    sensitivity: $meta->sensitivity,
                    description: $meta->description,
                    category: $meta->category,
                    retentionDays: $meta->retentionDays,
                );
            }
        }

        return $this->cache[$name] = $def;
    }
}
