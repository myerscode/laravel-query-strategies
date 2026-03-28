<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\ScopeClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class ScopeFilterStrategy extends Strategy
{
    #[Override]
    protected array $config = [
        'name' => [],
        'active' => [
            'default' => ScopeClause::class,
        ],
        'starts_before' => [
            'default' => ScopeClause::class,
        ],
        'created_between' => [
            'default' => ScopeClause::class,
        ],
    ];
}
