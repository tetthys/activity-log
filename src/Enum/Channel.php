<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Enum;

enum Channel: string
{
    case Web = 'web';
    case Api = 'api';
    case Cli = 'cli';
    case Worker = 'worker';
    case Unknown = 'unknown';
}
