<?php

namespace Tests\Support\Strategies;

use Illuminate\Database\Eloquent\Builder;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Closure;

class DefaultFilterStrategy extends Strategy
{
    protected array $config = [
        'name',
    ];

    /** @var array<int, Closure> */
    protected array $defaultFilters = [];

    public function __construct()
    {
        $this->defaultFilters = [
            static fn (Builder $builder): Builder => $builder->where('active', true),
        ];

        parent::__construct();
    }
}
