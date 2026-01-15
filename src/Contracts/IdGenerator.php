<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Contracts;

interface IdGenerator
{
    public function generate(): string;
}
