<?php

declare(strict_types=1);

namespace Tetthys\ActivityLog\Model;

final readonly class CountPlan
{
    /**
     * @param list<CountRule> $rules
     */
    public function __construct(
        public array $rules = [],
    ) {}
}
