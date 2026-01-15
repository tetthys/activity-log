<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\MetadataSanitizer;
use Tetthys\ActivityLog\Enum\Sensitivity;

final class FailSoftMetadataSanitizer implements MetadataSanitizer
{
    /** @var array<string,true> */
    private array $maskedKeys;

    /**
     * @param string[] $maskedKeys
     */
    public function __construct(array $maskedKeys = ['password', 'token', 'secret', 'authorization'])
    {
        $out = [];
        foreach ($maskedKeys as $k) {
            $out[strtolower($k)] = true;
        }
        $this->maskedKeys = $out;
    }

    public function sanitize(array $metadata, Sensitivity $sensitivity): array
    {
        $normalized = $this->normalize($metadata, $sensitivity);

        // Best-effort JSON check (never throw)
        try {
            json_encode($normalized, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            // If encoding fails, drop metadata entirely rather than failing the request
            return [];
        }

        return $normalized;
    }

    private function normalize(mixed $value, Sensitivity $sensitivity): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        }

        if ($value instanceof \JsonSerializable) {
            return $this->normalize($value->jsonSerialize(), $sensitivity);
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        if (is_array($value)) {
            $out = [];

            foreach ($value as $k => $v) {
                if (!is_int($k) && !is_string($k)) {
                    continue;
                }

                // Mask well-known sensitive keys
                if (is_string($k) && isset($this->maskedKeys[strtolower($k)])) {
                    $out[$k] = '[REDACTED]';
                    continue;
                }

                // If sensitivity is high, be stricter (example policy)
                if ($sensitivity === Sensitivity::Security && is_string($k) && str_contains(strtolower($k), 'email')) {
                    $out[$k] = '[REDACTED]';
                    continue;
                }

                $out[$k] = $this->normalize($v, $sensitivity);
            }

            return $out;
        }

        return null;
    }
}
