<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;

class BasicConfigQueryStrategy extends Strategy
{

    /**
     * {@inheritDoc}
     */
    public $config = [
        'foo',
        'bar',
        'hello',
        'world',
    ];
}
