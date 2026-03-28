<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Myerscode\Laravel\QueryStrategies\Facades\Query;

if (!function_exists('filter')) {

    /**
     * @param Builder<Model>|Model|string $builderOrModel
     */
    function filter(Builder|Model|string $builderOrModel): FilterBuilder
    {
        return Query::filter($builderOrModel);
    }
}
