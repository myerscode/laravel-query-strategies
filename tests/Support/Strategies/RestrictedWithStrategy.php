<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class RestrictedWithStrategy extends Strategy
{
    #[Override]
    protected array $canWith = [
        'owner',
    ];

    #[Override]
    protected array $config = [
        'foo' => [],
    ];
}
