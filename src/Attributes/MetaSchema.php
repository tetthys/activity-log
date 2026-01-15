<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
final class MetaSchema
{
    public function __construct(
        public string $key,
        public string $type = 'string', // string|int|float|bool|array|object
        public bool $required = false,
        public int $maxLen = 0,         // 0 means no limit
    ) {}
}
