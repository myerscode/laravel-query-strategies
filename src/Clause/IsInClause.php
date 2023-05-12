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
            array_walk($values, static function ($value) use (&$whereIn) : void {
                $whereIn = [...$whereIn, ...array_filter(explode(',', (string) $value))];
            });
            $builder->whereIn($column, $whereIn);
        }

        return $builder;
    }
}
