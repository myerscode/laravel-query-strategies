<?php

namespace DummyNamespace;

use Illuminate\Database\Eloquent\Builder;
use Myerscode\Laravel\QueryStrategies\Clause\AbstractClause;

class DummyClass extends AbstractClause
{

    /**
     * {@inheritdoc}
     */
    public function filter(Builder $builder, $value, $column)
    {
        return $builder;
    }
}
