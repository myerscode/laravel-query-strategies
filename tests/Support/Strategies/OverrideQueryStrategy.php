<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;

class OverrideQueryStrategy extends Strategy
{

    /**
     * {@inheritDoc}
     */
    public $config = [
        'foo' => [
            'column' => 'foo',
            'default' => DoesNotEqualClause::class,
            'disabled' => [
                'equals',
            ]
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
    public $limitTo = 5;

    /**
     * {@inheritDoc}
     */
    public $maxLimit = 50;

    /**
     * {@inheritDoc}
     */
    protected $canOrderBy = [
        'id',
        'name',
        'likes',
        'created',
    ];
}
