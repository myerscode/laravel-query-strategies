<?php

namespace Myerscode\Laravel\QueryStrategies;

use Myerscode\Laravel\QueryStrategies\Facades\Query;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('filter')) {

    function filter(Builder|Model|string $builderOrModel): FilterBuilder
    {
        return Query::filter($builderOrModel);
    }
}
