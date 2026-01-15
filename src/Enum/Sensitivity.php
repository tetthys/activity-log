<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Enum;

enum Sensitivity: string
{
    case Normal = 'normal';
    case Sensitive = 'sensitive';
    case Security = 'security';
}
