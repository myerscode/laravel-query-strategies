<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ScopeClause extends AbstractClause
{
    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function filter(Builder $builder, mixed $value, string $column): Builder
    {
        $scope = Str::camel($column);

        $values = is_array($value) ? $value : [$value];

        // Flatten comma-separated values
        $flattened = [];
        foreach ($values as $v) {
            if (is_string($v) && str_contains($v, ',')) {
                array_push($flattened, ...explode(',', $v));
            } else {
                $flattened[] = $v;
            }
        }

        // Filter out empty values
        $flattened = array_filter($flattened, static fn ($v): bool => $v !== '' && $v !== null);

        if ($flattened === []) {
            return $builder;
        }

        $builder->{$scope}(...$flattened);

        return $builder;
    }
}
