<?php

namespace Tests\Support\Clause;

use Illuminate\Database\Eloquent\Builder;
use Myerscode\Laravel\QueryStrategies\Clause\AbstractClause;

class LookupClause extends AbstractClause
{

    /**
     * {@inheritdoc}
     */
    public function filter(Builder $builder, $value, $column)
    {
        if (!empty($value)) {
            $values = is_array($value) ? $value : [$value];
            $builder->where($column, '=', implode('&', $values));
        }

        return $builder;
    }
}
