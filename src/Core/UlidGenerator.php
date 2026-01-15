<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Core;

use Tetthys\ActivityLog\Contracts\IdGenerator;

final class UlidGenerator implements IdGenerator
{
    public function generate(): string
    {
        // Minimal ULID generator (Crockford Base32). Good for ordering and uniqueness.
        $time = (int) floor(microtime(true) * 1000);
        $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

        $encode = function (int $value, int $length) use ($alphabet): string {
            $out = '';
            for ($i = 0; $i < $length; $i++) {
                $out = $alphabet[$value % 32] . $out;
                $value = intdiv($value, 32);
            }
            return $out;
        };

        $timePart = $encode($time, 10);

        $rand = random_bytes(10); // 80 bits
        $bits = '';
        foreach (str_split($rand) as $ch) {
            $bits .= str_pad(decbin(ord($ch)), 8, '0', STR_PAD_LEFT);
        }

        $randPart = '';
        for ($i = 0; $i < 16; $i++) {
            $chunk = substr($bits, $i * 5, 5);
            $randPart .= $alphabet[bindec($chunk)];
        }

        return $timePart . $randPart;
    }
}
