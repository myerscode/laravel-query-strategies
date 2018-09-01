<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\BeginsWithClause;
use Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause;
use Myerscode\Laravel\QueryStrategies\Clause\EndsWithClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;

class ComplexConfigQueryStrategy extends Strategy
{

    /**
     * {@inheritDoc}
     */
    public $config = [
        'foo' => [
            'column' => 'foo',
            'methods' => [
                'begins' => BeginsWithClause::class,
                'ends' => EndsWithClause::class,
            ]
        ],
        'bar' => [
            'column' => 'bar',
        ],
        'foobar' => [
        ],
        'hello' => [
            'default' => DoesNotEqualClause::class,
            'disabled' => [
                'equals',
            ]
        ],
        'barfoo' => [
            'column' => 'bar_foo',
            'aliases' => [
                'bf',
                'barf',
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    protected $canOrderBy = [
        'id',
        'name',
        'date'
    ];
}
