<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CallbackClause extends AbstractClause
{
    public function __construct(private readonly Closure $callback)
    {
    }

    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function filter(Builder $builder, mixed $value, string $column): Builder
    {
        ($this->callback)($builder, $value, $column);

        return $builder;
    }
}
