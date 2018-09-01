<?php

use Myerscode\Laravel\QueryStrategies\Facades\Query;
use Myerscode\Laravel\QueryStrategies\FilterBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('filter')) {

    /**
     * @param  Builder|Model|string $builderOrModel
     *
     * @return FilterBuilder
     */
    function filter($builderOrModel): FilterBuilder
    {
        return Query::filter($builderOrModel);
    }
}
