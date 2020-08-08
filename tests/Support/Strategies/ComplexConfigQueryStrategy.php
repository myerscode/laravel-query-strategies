<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\BeginsWithClause;
use Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause;
use Myerscode\Laravel\QueryStrategies\Clause\EndsWithClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Myerscode\Laravel\QueryStrategies\Transmute\BoolTransmute;
use Tests\Support\Clause\CustomMultiClause;

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
        'explodable' => [
            'explode' => true,
        ],
        'exploding' => [
            'explode' => true,
            'delimiter' => '||',
        ],
        'multi_override' => [
            'multi' => CustomMultiClause::class,
            'explode' => true,
        ],
        'transmute_me' => [
            'transmute' => BoolTransmute::class,
        ],
        'can_split' => [
            'explode' => true,
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
