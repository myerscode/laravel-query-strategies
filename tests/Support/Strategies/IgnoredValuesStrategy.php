<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class IgnoredValuesStrategy extends Strategy
{
    #[Override]
    protected array $config = [
        'name' => [
            'ignore' => ['-1', 'all'],
        ],
        'status' => [
            'ignore' => [null],
        ],
        'type' => [],
    ];
}
