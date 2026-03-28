<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Myerscode\Laravel\QueryStrategies\Facades\Query;

/**
 * @param Builder<Model>|Model|string $builderOrModel
 */
function filter(Builder|Model|string $builderOrModel): FilterBuilder
{
    return Query::filter($builderOrModel);
}
