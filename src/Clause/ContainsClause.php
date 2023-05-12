<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;

class ContainsClause extends AbstractClause
{

    /**
     * {@inheritdoc}
     */
    public function filter(Builder $builder, $value, $column)
    {
        if (!empty($value)) {
            $values = is_array($value) ? $value : [$value];
            collect($values)->each(static function ($value) use ($column, $builder) : void {
                $builder->where($column, 'like', '%' . $value . '%');
            });
        }

        return $builder;
    }
}
