<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class DefaultValueStrategy extends Strategy
{
    #[Override]
    protected array $config = [
        'name' => [],
        'active' => [
            'defaultValue' => '1',
        ],
        'status' => [
            'defaultValue' => 'published',
        ],
        'nullable' => [
            'defaultValue' => null,
        ],
    ];
}
