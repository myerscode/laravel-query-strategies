<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Myerscode\Laravel\QueryStrategies\Clause\ClauseInterface;
use Myerscode\Laravel\QueryStrategies\Clause\EqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsInClause;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;

class Filter
{

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @var array
     */
    private $query;

    /**
     * If no filter is set or no default is set in strategy, use this
     *
     * @var string
     */
    private $defaultFilter = EqualsClause::class;

    /**
     * If a parameter allows multiple values use this class
     *
     * @var string
     */
    private $multiFilter = IsInClause::class;

    /**
     * @var string
     */
    private $orderKey = 'order';

    /**
     * @var string
     */
    private $sortKey = 'sort';

    /**
     * @var string
     */
    private $limitKey = 'limit';

    /**
     * @var string
     */
    private $pageKey = 'page';

    /**
     * @var string
     */
    private $with = 'with';


    public function __construct(Builder $builder, StrategyInterface $strategy, array $query, array $config = [])
    {
        $this->builder = $builder;
        $this->strategy = $strategy;
        $this->query = $query;
        $this->setConfig($config);
    }

    /**
     * Set any configurable options
     *
     * @param array $config
     */
    private function setConfig(array $config)
    {
        $this->orderKey = $config['order'] ?? 'order';
        $this->sortKey = $config['sort'] ?? 'sort';
        $this->limitKey = $config['limit'] ?? 'limit';
        $this->pageKey = $config['page'] ?? 'page';
        $this->with = $config['with'] ?? 'with';
    }

    /**
     * The builder Distill will apply a strategy to
     *
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this->builder;
    }

    /**
     * Apply Filters, Includes, Ordering and Pagination
     *
     * @return LengthAwarePaginator
     */
    public function apply(): LengthAwarePaginator
    {
        $this->filter();
        $this->order();
        $this->limit();
        $this->with();
        return $this->paginate();
    }

    /**
     * Apply the filters to the Builder
     *
     * @return Filter
     */
    public function filter(): Filter
    {
        $filterKeys = array_keys($this->strategy->parameters());

        // get parameters that can be used to filter this query from the current request
        $parameters = collect($this->query)->only($filterKeys)->toArray();

        $massFilterKeys = collect($filterKeys)->map(function ($item) {
            return $item . '--filter';
        });

        $massFilters = collect($this->query)->only($massFilterKeys)->toArray();

        foreach ($parameters as $parameter => $values) {
            $parameterConf = $this->strategy->parameter($parameter);

            $filterValues = $this->prepareValues($values, $parameterConf->getDisabled());

            $methods = $this->getParameterMethods($parameter);

            $namedFilters = $this->findNameValues($filterValues);

            $defaultFilters = collect($filterValues)->except(array_keys($namedFilters))->toArray();

            $defaultFilter = $parameterConf->getDefault();

            if (empty($defaultFilter)) {
                $defaultFilter = $this->defaultFilter;
            }

            if (count($defaultFilters) > 1) {
                $defaultFilter = $this->multiFilter;
            }

            $massFilterKey = $parameterConf->getMassFilter();

            if ((isset($massFilters[$massFilterKey]) && isset($methods[$massFilters[$massFilterKey]]))) {
                $defaultFilter = $methods[$massFilters[$massFilterKey]];
            }

            $filtersToApply = [];

            foreach ($filterValues as $filterMethod => $value) {
                $filterClass = (isset($methods[$filterMethod])) ? $methods[$filterMethod] : $defaultFilter;
                $filtersToApply[$filterClass][] = $value;
            }

            $columnName = $parameterConf->getColumn() ?? null;

            $this->applyFilters($columnName, $filtersToApply);
        }

        return $this;
    }

    /**
     * Apply order and sorting rules to the query
     *
     * @return Filter
     */
    public function order(): Filter
    {
        $canOrderBy = $this->strategy->canOrderBy();

        $directions = ['asc', 'desc'];

        $defaultDirection = 'asc';

        $orderKey = $this->orderKey;

        $sortKey = $this->sortKey;

        $orderValues = $this->query[$orderKey] ?? [];

        if (empty($orderValues)) {
            return $this;
        }

        $sortValues = collect($this->query[$sortKey] ?? $defaultDirection);
        $defaultDirection = $sortValues
                ->filter(function ($value, $key) {
                    return is_int($key);
                })->pop() ?? $defaultDirection;

        $sortBy = $sortValues->filter(function ($value, $key) {
            return !is_int($key);
        });

        $orderBy = [];

        if (is_array($orderValues)) {
            $orderBy = collect($orderValues)->mapWithKeys(function ($value, $key) use ($sortBy, $defaultDirection) {
                if (is_int($key)) {
                    $direction = $sortBy->get($value) ?? $defaultDirection;
                    return [$value => $direction];
                } else {
                    if (is_array($value)) {
                        return collect($value)->mapWithKeys(function ($value) use ($key) {
                            return [$value => $key];
                        })->toArray();
                    } else {
                        return [$value => $key];
                    }
                }
            })->toArray();
        } else {
            $orderBy[strtolower($orderValues)] = strtolower($defaultDirection);
        }

        foreach (collect($orderBy)->only($canOrderBy) as $column => $dir) {
            $direction = (in_array($dir, $directions)) ? $dir : 'asc';
            $this->builder->orderBy($column, $direction);
        }

        return $this;
    }

    /**
     * Limit the amount of results returned
     *
     * @return Filter
     */
    public function limit(): Filter
    {
        $this->builder->limit($this->getLimitValue());

        return $this;
    }

    /**
     * Paginate the query using the strategy rules
     *
     * @return LengthAwarePaginator
     */
    public function paginate(): LengthAwarePaginator
    {
        /**
         * Get the current key value pairs currently used in the paginated query
         */
        $appends = array_diff_assoc($this->query, array_keys($this->strategy->parameters()));

        $perPage = $this->getLimitValue();

        $this->builder->limit($perPage);

        /**
         * @var $pagination LengthAwarePaginator
         */
        $pagination = $this->builder->paginate($perPage);

        $pagination->appends($appends);

        return $pagination;
    }

    /**
     * Apply eloquent withs for eager loading relationships
     *
     * @return Filter
     */
    public function with(): Filter
    {
        $pieces = $this->query[$this->with] ?? [];

        $with = array_filter(explode(',', implode(',', is_array($pieces) ? $pieces : [$pieces])));

        $this->builder->with($with);

        return $this;
    }

    /**
     * @param string $column
     * @param array $filters
     */
    protected function applyFilters(string $column, array $filters)
    {
        foreach ($filters as $filterClass => $filterValues) {
            $this->applyFilter($filterClass, $filterValues, $column);
        }
    }

    /**
     * Apply a filter clause to a builder
     *
     * @param  $class
     * @param  $value
     * @param  $column
     * @return Filter
     */
    public function applyFilter($class, $value, $column): Filter
    {
        if (class_exists($class) && ($filter = app($class)) instanceof ClauseInterface) {
            /**
             * @var $filter ClauseInterface
             */
            $filter->filter($this->builder, $value, $column);
        }

        return $this;
    }

    /**
     * Get the limit value for restricting result count
     *
     * @return int
     */
    private function getLimitValue(): int
    {
        $limitKey = $this->limitKey;

        /**
         * Get the number of items to return for the query
         *
         * @var int $limit
         * @var int $perPage
         */
        $limit = $this->query[$limitKey] ?? $this->strategy->limit();

        if (!is_numeric($limit) || $limit < 0) {
            $limit = $this->strategy->limit();
        }

        $perPage = ($limit <= $this->strategy->maxLimit()) ? $limit : $this->strategy->maxLimit();

        return $perPage;
    }

    /**
     * @param $values
     * @param $disabled
     * @return array
     */
    private function prepareValues($values, array $disabled): array
    {
        $filterValues = is_array($values) ? $values : [$values];

        // if there are any disabled filter clauses remove them
        if (!empty($disabled)) {
            // TODO remove need for collect
            $filterValues = collect($filterValues)->except($disabled)->all();
        }

        return $filterValues;
    }

    /**
     * @param $values
     * @return array
     */
    private function findNameValues($values): array
    {
        return array_filter($values, function ($key) {
            return !is_int($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get the clauses that the parameter can apply to the query
     *
     * @param $parameter
     * @return array
     */
    public function getParameterMethods($parameter): array
    {
        $filters = $this->strategy->parameter($parameter)->getMethods();
        $except = $this->strategy->parameter($parameter)->getDisabled();
        return array_diff_assoc(array_merge($this->strategy->defaultMethods(), $filters), array_keys($except));
    }
}
