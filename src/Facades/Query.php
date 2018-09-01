<?php

namespace Myerscode\Laravel\QueryStrategies\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Myerscode\Laravel\QueryStrategies\FilterBuilder
 */
class Query extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Query';
    }
}
