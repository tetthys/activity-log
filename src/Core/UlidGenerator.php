<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\IdGenerator;

/**
 * Minimal ULID generator (pure PHP).
 */
final class UlidGenerator implements IdGenerator
{
    private const ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    public function generate(): string
    {
        $time = (int) (microtime(true) * 1000);

        $timeBytes = '';
        for ($i = 5; $i >= 0; $i--) {
            $timeBytes .= chr(($time >> ($i * 8)) & 0xFF);
        }

        return $this->encode($timeBytes . random_bytes(10));
    }

    private function encode(string $bytes): string
    {
        $bits = '';
        foreach (str_split($bytes) as $b) {
            $bits .= str_pad(decbin(ord($b)), 8, '0', STR_PAD_LEFT);
        }

        $bits = str_pad($bits, 130, '0', STR_PAD_RIGHT);

        $out = '';
        for ($i = 0; $i < 130; $i += 5) {
            $out .= self::ALPHABET[bindec(substr($bits, $i, 5))];
        }

        return $out;
    }
}
