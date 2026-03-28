<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\ContainsClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class RelationshipFilterStrategy extends Strategy
{
    #[Override]
    protected array $config = [
        'name' => [],
        'owner.name' => [
            'column' => 'owner.name',
        ],
        'owner_email' => [
            'column' => 'owner.email',
            'default' => ContainsClause::class,
        ],
    ];
}
