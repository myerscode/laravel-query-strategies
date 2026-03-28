<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BeginsWithClause extends AbstractClause
{
    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function filter(Builder $builder, mixed $value, string $column): Builder
    {
        if (!empty($value)) {
            $values = is_array($value) ? $value : [$value];
            collect($values)->each(static function (string $value) use ($column, $builder): void {
                $builder->where($column, 'like', $value . '%');
            });
        }

        return $builder;
    }
}
