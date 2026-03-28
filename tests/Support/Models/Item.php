<?php

namespace Tests\Support\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public function owner()
    {
        return $this->hasOne(Owner::class);
    }

    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('active', true);
    }

    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function scopeCreatedBetween(Builder $builder, string $from, string $to): Builder
    {
        return $builder->whereBetween('created_at', [$from, $to]);
    }

    /**
     * @param Builder<Model> $builder
     * @return Builder<Model>
     */
    public function scopeStartsBefore(Builder $builder, string $date): Builder
    {
        return $builder->where('starts_at', '<=', $date);
    }
}
