<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;

class RelationshipOrderStrategy extends Strategy
{
    protected array $canOrderBy = ['id', 'name', 'owner.name'];

    protected array $config = [
        'name',
    ];
}
