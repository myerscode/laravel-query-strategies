<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;

class BeginsWithClause extends AbstractClause
{

    /**
     * {@inheritdoc}
     */
    public function filter(Builder $builder, $value, $column)
    {
        if (!empty($value)) {
            $values = is_array($value) ? $value : [$value];
            collect($values)->each(function ($value) use ($column, $builder) {
                $builder->where($column, 'like', $value . '%');
            });
        }

        return $builder;
    }
}
