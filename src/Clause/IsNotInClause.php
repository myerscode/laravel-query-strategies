<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;

class IsNotInClause extends AbstractClause
{

    /**
     * {@inheritdoc}
     */
    public function filter(Builder $builder, $value, $column)
    {
        if (!empty($value)) {
            $values = is_array($value) ? $value : [$value];
            $notIn = [];
            array_walk($values, static function ($value) use (&$notIn) : void {
                $notIn = [...$notIn, ...array_filter(explode(',', (string) $value))];
            });
            $builder->whereNotIn($column, $notIn);
        }

        return $builder;
    }
}
