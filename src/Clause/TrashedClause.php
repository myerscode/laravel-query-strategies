<?php

namespace Myerscode\Laravel\QueryStrategies\Clause;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrashedClause extends AbstractClause
{
    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function filter(Builder $builder, mixed $value, string $column): Builder
    {
        $value = is_array($value) ? ($value[0] ?? '') : $value;
        $value = strtolower(trim((string) $value));

        $model = $builder->getModel();
        $deletedAtColumn = method_exists($model, 'getQualifiedDeletedAtColumn')
            ? $model->getQualifiedDeletedAtColumn()
            : $model->getTable() . '.deleted_at';

        return match ($value) {
            'with' => $builder->withoutGlobalScope(SoftDeletingScope::class),
            'only' => $builder->withoutGlobalScope(SoftDeletingScope::class)
                ->whereNotNull($deletedAtColumn),
            default => $builder,
        };
    }
}
