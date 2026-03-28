<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;

class AppendStrategy extends Strategy
{
    protected array $allowedAppends = ['display_name'];

    protected array $config = [
        'name',
    ];
}
