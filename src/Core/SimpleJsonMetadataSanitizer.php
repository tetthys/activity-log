<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\MetadataSanitizer;

final class SimpleJsonMetadataSanitizer implements MetadataSanitizer
{
    public function sanitize(array $metadata): array
    {
        $normalized = $this->normalize($metadata);
        json_encode($normalized, JSON_THROW_ON_ERROR);
        return $normalized;
    }

    private function normalize(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                if (is_int($k) || is_string($k)) {
                    $out[$k] = $this->normalize($v);
                }
            }
            return $out;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        }

        if ($value instanceof \JsonSerializable) {
            return $this->normalize($value->jsonSerialize());
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return null;
    }
}
