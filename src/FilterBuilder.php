<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotFoundException;
use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotSetException;
use Myerscode\Laravel\QueryStrategies\Exceptions\FilterStrategyNotFoundException;
use Myerscode\Laravel\QueryStrategies\Exceptions\InvalidStrategyException;
use Myerscode\Laravel\QueryStrategies\Strategies\DefaultModelStrategy;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;

class FilterBuilder
{
    /** @var Builder<Model>|null */
    private ?Builder $builder = null;

    private ?Model $model = null;


    public function __construct(private readonly Request $request, private readonly StrategyManager $strategyManager)
    {
    }

    /**
     * @return Builder<Model>|null
     */
    public function builder(): ?Builder
    {
        return $this->builder;
    }

    /**
     * The config the will be built with
     * @return array{order: mixed, sort: mixed, limit: mixed, page: mixed, with: mixed}
     */
    public function config(): array
    {
        return [
            'fields' => config('query-strategies.parameters.fields', 'fields'),
            'order' => config('query-strategies.parameters.order', 'order'),
            'sort' => config('query-strategies.parameters.sort', 'sort'),
            'limit' => config('query-strategies.parameters.limit', 'limit'),
            'page' => config('query-strategies.parameters.page', 'page'),
            'with' => config('query-strategies.parameters.with', 'with'),
        ];
    }

    /**
     * Set the builder which we will be used for querying
     *
     * @param Builder<Model>|Model|string $builderOrModel
     * @throws BuilderNotFoundException
     * @throws BuilderNotSetException
     */
    public function filter(Builder|Model|string $builderOrModel): FilterBuilder
    {
        $this->setBuilder($builderOrModel);

        return $this;
    }

    /**
     * @param string|StrategyInterface|array<int|string, array<string, mixed>|string>|null $possibleStrategy
     * @return LengthAwarePaginator<int, mixed>
     * @throws BuilderNotSetException
     * @throws FilterStrategyNotFoundException
     * @throws InvalidStrategyException
     */
    public function results(string|StrategyInterface|array|null $possibleStrategy = null): LengthAwarePaginator
    {
        if (is_null($possibleStrategy) && ($this->model instanceof Model) && isset($this->model->strategyConfig)) {
            $possibleStrategy = DefaultModelStrategy::fromConfig($this->model->strategyConfig);
        }

        if (is_null($possibleStrategy)) {
            throw new BuilderNotSetException('No strategy provided and model has no strategyConfig');
        }

        return $this->with($possibleStrategy)->apply();
    }

    /**
     * Apply a possible strategy by name or a given class
     *
     * @param string|StrategyInterface|array<int|string, array<string, mixed>|string> $possibleStrategy
     *
     * @throws BuilderNotSetException
     * @throws FilterStrategyNotFoundException
     * @throws InvalidStrategyException
     */
    public function with(string|StrategyInterface|array $possibleStrategy): Filter
    {
        if (!$this->builder instanceof Builder) {
            throw new BuilderNotSetException();
        }

        if (is_array($possibleStrategy)) {
            $possibleStrategy = DefaultModelStrategy::fromConfig($possibleStrategy);
        }

        $strategy = $this->strategyManager->findStrategy($possibleStrategy);
        return new Filter($this->builder, $strategy, $this->request->query->all(), $this->config());
    }

    protected function hasFilterTrait(): bool
    {
        if (!$this->model instanceof Model) {
            return false;
        }

        $usedTraits = class_uses($this->model);

        return $usedTraits && in_array(IsFilterableTrait::class, $usedTraits);
    }

    /**
     * Get a query builder via the passed by checking if its a model or already a builder
     *
     * @param Builder<Model>|Model|string $builderOrModel
     * @throws BuilderNotFoundException
     * @throws BuilderNotSetException
     */
    private function setBuilder(Builder|Model|string $builderOrModel): void
    {
        if (empty($builderOrModel)) {
            throw new BuilderNotSetException();
        }

        if ($builderOrModel instanceof Builder) {
            $this->builder = $builderOrModel;
        } elseif ($builderOrModel instanceof Model) {
            $this->model = $builderOrModel;
            $this->builder = $builderOrModel->newQuery();
        } elseif (class_exists($builderOrModel) && ($model = app($builderOrModel)) instanceof Model) {
            $this->model = $model;
            $this->builder = $model->query();
        } else {
            throw new BuilderNotFoundException();
        }
    }
}
