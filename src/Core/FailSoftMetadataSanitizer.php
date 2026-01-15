<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\MetadataSanitizer;

final class FailSoftMetadataSanitizer implements MetadataSanitizer
{
    /** @var array<string, true> */
    private array $blockedKeys;

    public function __construct(
        array $blockedKeys = ['password', 'passwd', 'token', 'secret', 'authorization', 'cookie'],
        private int $maxDepth = 6,
        private int $maxString = 2000,
        private int $maxItems = 200,
    ) {
        $this->blockedKeys = array_fill_keys(array_map('strtolower', $blockedKeys), true);
    }

    public function sanitize(array $metadata): array
    {
        return $this->walk($metadata, 0);
    }

    private function scrub(mixed $value, int $depth): mixed
    {
        if ($depth > $this->maxDepth) {
            return '[truncated-depth]';
        }

        if (is_string($value)) {
            if (mb_strlen($value) > $this->maxString) {
                return mb_substr($value, 0, $this->maxString) . '…[truncated]';
            }
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value) || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            return $this->walk($value, $depth + 1);
        }

        if (is_object($value)) {
            if ($value instanceof \Stringable) {
                return (string) $value;
            }
            return '[object:' . $value::class . ']';
        }

        return '[unsupported]';
    }

    private function walk(array $arr, int $depth): array
    {
        $out = [];
        $count = 0;

        foreach ($arr as $k => $v) {
            if ($count++ >= $this->maxItems) {
                $out['__truncated__'] = '[too-many-items]';
                break;
            }

            $keyStr = is_string($k) ? strtolower($k) : null;
            if ($keyStr !== null && isset($this->blockedKeys[$keyStr])) {
                $out[$k] = '[redacted]';
                continue;
            }

            $out[$k] = $this->scrub($v, $depth);
        }

        return $out;
    }
}
