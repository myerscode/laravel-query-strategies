<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class BasicConfigQueryStrategy extends Strategy
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected array $config = [
        'foo',
        'bar',
        'hello',
        'world',
    ];
}
