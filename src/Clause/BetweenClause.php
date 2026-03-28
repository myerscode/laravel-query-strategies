<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BetweenClause extends AbstractClause
{
    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function filter(Builder $builder, mixed $value, string $column): Builder
    {
        if (is_array($value)) {
            // Flatten: ['10,100'] → ['10', '100'] or ['10', '100'] stays as-is
            $values = [];
            foreach ($value as $v) {
                if (is_string($v) && str_contains($v, ',')) {
                    array_push($values, ...explode(',', $v));
                } else {
                    $values[] = $v;
                }
            }
        } else {
            $values = explode(',', (string) $value);
        }

        if (count($values) === 2) {
            $builder->whereBetween($column, [$values[0], $values[1]]);
        }

        return $builder;
    }
}
