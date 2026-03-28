<?php

namespace Tests\Support\Strategies;

use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Override;

class FieldSelectionStrategy extends Strategy
{
    #[Override]
    protected array $allowedFields = [
        'id',
        'name',
        'email',
    ];

    #[Override]
    protected array $config = [
        'name' => [],
    ];
}
