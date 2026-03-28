<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;

class BasicConfigQueryStrategy extends Strategy
{

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public $config = [
        'foo',
        'bar',
        'hello',
        'world',
    ];
}
