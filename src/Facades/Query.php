<?php

namespace Myerscode\Laravel\QueryStrategies\Facades;

use Illuminate\Support\Facades\Facade;
use Myerscode\Laravel\QueryStrategies\FilterBuilder;
use Override;

/**
 * @method static FilterBuilder filter(mixed $builderOrModel)
 *
 * @see FilterBuilder
 */
class Query extends Facade
{
    /**
     * Get the registered name of the component.
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'Query';
    }
}
