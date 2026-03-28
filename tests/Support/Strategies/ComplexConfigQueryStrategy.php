<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\BeginsWithClause;
use Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause;
use Myerscode\Laravel\QueryStrategies\Clause\EndsWithClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Myerscode\Laravel\QueryStrategies\Transmute\BoolTransmute;
use Tests\Support\Clause\CustomMultiClause;
use Tests\Support\Clause\LookupClause;
use Override;

class ComplexConfigQueryStrategy extends Strategy
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected array $canOrderBy = [
        'id',
        'name',
        'date',
    ];

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected array $config = [
        'foo' => [
            'column' => 'foo',
            'methods' => [
                'begins' => BeginsWithClause::class,
                'ends' => EndsWithClause::class,
            ],
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
            ],
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
        'override_this' => [
            'multi' => CustomMultiClause::class,
            'explode' => true,
            'methods' => [
                'lookup' => LookupClause::class,
            ],
        ],
    ];
}
