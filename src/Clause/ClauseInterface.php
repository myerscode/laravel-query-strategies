<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;

interface ClauseInterface
{

    /**
     * Apply query filters to a builder
     *
     * @param  $value
     * @param  $column
     * @return mixed
     */
    public function filter(Builder $builder, $value, $column);
}
