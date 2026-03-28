<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IsInClause extends AbstractClause
{
    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function filter(Builder $builder, mixed $value, string $column): Builder
    {
        if (!empty($value)) {
            $values = is_array($value) ? $value : [$value];
            $whereIn = [];
            array_walk($values, static function ($value) use (&$whereIn): void {
                $whereIn = [...$whereIn, ...array_filter(explode(',', (string) $value))];
            });
            $builder->whereIn($column, $whereIn);
        }

        return $builder;
    }
}
