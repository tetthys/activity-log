<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\DTO;

use BackedEnum;

/**
 * Represents the subject of the activity.
 */
final class SubjectRef
{
    public function __construct(
        public readonly BackedEnum|string $type,
        public readonly string $id,
    ) {}

    /**
     * @return array{type:string,id:string}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type instanceof BackedEnum
                ? (string) $this->type->value
                : (string) $this->type,
            'id' => $this->id,
        ];
    }
}
