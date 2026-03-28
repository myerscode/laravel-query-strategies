<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class AggregateIncludeStrategy extends Strategy
{
    #[Override]
    protected array $canWith = [
        'owner',
    ];

    #[Override]
    protected array $aggregateIncludes = [
        'ownerCount' => ['type' => 'count', 'relationship' => 'owner'],
        'ownerExists' => ['type' => 'exists', 'relationship' => 'owner'],
    ];

    #[Override]
    protected array $config = [
        'name' => [],
    ];
}
