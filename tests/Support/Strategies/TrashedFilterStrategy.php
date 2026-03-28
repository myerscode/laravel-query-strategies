<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\TrashedClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class TrashedFilterStrategy extends Strategy
{
    #[Override]
    protected array $config = [
        'name' => [],
        'trashed' => [
            'default' => TrashedClause::class,
        ],
    ];
}
