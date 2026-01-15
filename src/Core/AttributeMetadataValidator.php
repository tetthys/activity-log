<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Attributes\MetaSchema;
use Tetthys\ActivityLog\Contracts\MetadataValidator;
use Tetthys\ActivityLog\Support\Types;

final class AttributeMetadataValidator implements MetadataValidator
{
    /** @var array<string, array<int, MetaSchema>> */
    private array $cache = [];

    public function validate(\UnitEnum $action, array $metadata): void
    {
        $schemas = $this->schemasFor($action);

        // Fast path: no schemas means no validation.
        if ($schemas === []) {
            return;
        }

        // Required checks + type checks.
        foreach ($schemas as $schema) {
            $hasKey = array_key_exists($schema->key, $metadata);

            if ($schema->required && !$hasKey) {
                throw new \InvalidArgumentException("Missing required metadata key '{$schema->key}'.");
            }

            if (!$hasKey) {
                continue;
            }

            $value = $metadata[$schema->key];

            if (!Types::isType($value, $schema->type)) {
                $got = gettype($value);
                throw new \InvalidArgumentException("Metadata key '{$schema->key}' expects type '{$schema->type}', got '{$got}'.");
            }

            if ($schema->type === 'string' && $schema->maxLen > 0 && is_string($value)) {
                if (mb_strlen($value) > $schema->maxLen) {
                    throw new \InvalidArgumentException("Metadata key '{$schema->key}' exceeds maxLen {$schema->maxLen}.");
                }
            }
        }
    }

    /**
     * @return array<int, MetaSchema>
     */
    private function schemasFor(\UnitEnum $action): array
    {
        $key = $action::class . '::' . $action->name;

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $ref = new \ReflectionEnumUnitCase($action::class, $action->name);
        $attrs = $ref->getAttributes(MetaSchema::class);

        $schemas = [];
        foreach ($attrs as $attr) {
            /** @var MetaSchema $s */
            $s = $attr->newInstance();
            $schemas[] = $s;
        }

        return $this->cache[$key] = $schemas;
    }
}
