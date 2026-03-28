<?php

namespace Myerscode\Laravel\QueryStrategies\Stubs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Myerscode\Laravel\QueryStrategies\Clause\AbstractClause;

class DummyClass extends AbstractClause
{
    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function filter(Builder $builder, mixed $value, string $column): Builder
    {
        return $builder;
    }
}
