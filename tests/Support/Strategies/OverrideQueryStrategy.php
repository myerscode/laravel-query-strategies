<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class OverrideQueryStrategy extends Strategy
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected array $canOrderBy = [
        'id',
        'name',
        'likes',
        'created',
    ];

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected array $config = [
        'foo' => [
            'column' => 'foo',
            'default' => DoesNotEqualClause::class,
            'disabled' => [
                'equals',
            ],
        ],
        'bar',
        'foobar' => [
            'column' => 'foobar',
            'aliases' => [
                'fb',
                'fbar',
            ],
        ],
    ];


    /**
     * {@inheritDoc}
     */
    #[Override]
    protected int $limitTo = 5;

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected int $maxLimit = 50;
}
