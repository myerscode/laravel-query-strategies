<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;

class IsInClause extends AbstractClause
{

    /**
     * {@inheritdoc}
     */
    public function filter(Builder $builder, $value, $column)
    {
        if (!empty($value)) {
            $values = is_array($value) ? $value : [$value];
            $whereIn = [];
            array_walk($values, function ($value) use (&$whereIn) {
                $whereIn = array_merge($whereIn, array_filter(explode(',', $value)));
            });
            $builder->whereIn($column, $whereIn);
        }

        return $builder;
    }
}
