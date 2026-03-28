<?php

namespace Tests\Support\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['name', 'likes'];

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

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => 'Item #' . $this->id,
        );
    }
}
