<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Support;

final class Types
{
    public static function isType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'int'    => is_int($value),
            'float'  => is_float($value) || is_int($value),
            'bool'   => is_bool($value),
            'array'  => is_array($value),
            'object' => is_array($value) || is_object($value),
            default  => false,
        };
    }
}
