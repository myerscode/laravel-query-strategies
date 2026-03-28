<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IsNotInClause extends AbstractClause
{
    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function filter(Builder $builder, mixed $value, string $column): Builder
    {
        if (!empty($value)) {
            $values = is_array($value) ? $value : [$value];
            $notIn = [];
            array_walk($values, static function ($value) use (&$notIn): void {
                $notIn = [...$notIn, ...array_filter(explode(',', (string) $value))];
            });
            $builder->whereNotIn($column, $notIn);
        }

        return $builder;
    }
}
