<?php

namespace Myerscode\Laravel\QueryStrategies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Myerscode\Laravel\QueryStrategies\Clause\ClauseInterface;
use Myerscode\Laravel\QueryStrategies\Clause\EqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsInClause;
use Myerscode\Laravel\QueryStrategies\Strategies\Parameter;
use Myerscode\Laravel\QueryStrategies\Strategies\Property;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;
use Myerscode\Laravel\QueryStrategies\Transmute\TransmuteInterface;

class Filter
{

    /**
     * If no filter is set or no default is set in strategy, use this
     */
    private string $defaultFilter = EqualsClause::class;

    /**
     * If a parameter allows multiple values use this class
     */
    private string $defaultMultiFilter = IsInClause::class;

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
    private $with = 'with';


    public function __construct(private readonly Builder $builder, private readonly StrategyInterface $strategy, private array $query, array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Set any configurable options
     */
    private function setConfig(array $config): void
    {
        $this->orderKey = $config['order'] ?? 'order';
        $this->sortKey = $config['sort'] ?? 'sort';
        $this->limitKey = $config['limit'] ?? 'limit';
        $this->with = $config['with'] ?? 'with';
    }

    /**
     * The builder Distill will apply a strategy to
     */
    public function builder(): Builder
    {
        return $this->builder;
    }

    /**
     * Apply Filters, Includes, Ordering and Pagination
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
     */
    public function filter(): Filter
    {

        $parameters = $this->filterParameters();

        $overrideFilters = $this->parameterOverrides();

        foreach ($parameters as $parameter => $values) {
            $parameterConf = $this->strategy->parameter($parameter);

            $filterValues = $this->prepareValues($values, $parameterConf);

            $methods = $this->getParameterMethods($parameter);

            $namedFilters = $this->findNameValues($filterValues);

            $defaultFilters = collect($filterValues)->except(array_keys($namedFilters))->toArray();

            $defaultFilter = $parameterConf->defaultMethod();

            if (empty($defaultFilter)) {
                $defaultFilter = $this->defaultFilter;
            }

            if (count($defaultFilters) > 1) {
                $defaultFilter = $parameterConf->multiMethod();
                if (empty($defaultFilter)) {
                    $defaultFilter = $this->defaultMultiFilter;
                }
            }

            $overrideKey = $parameterConf->operatorOverride();

            if ((isset($overrideFilters[$overrideKey]) && isset($methods[$overrideFilters[$overrideKey]]))) {
                $defaultFilter = $methods[$overrideFilters[$overrideKey]];
            }

            $filtersToApply = [];

            foreach ($filterValues as $filterMethod => $filterValue) {
                $filterClass = $methods[$filterMethod] ?? $defaultFilter;
                $filtersToApply[$filterClass] ??= [];
                $filterValue = is_array($filterValue) ? $filterValue : [$filterValue];
                $filtersToApply[$filterClass] = array_merge($filtersToApply[$filterClass], $filterValue);
            }

            $columnName = $parameterConf->column() ?? null;

            $this->applyFilters($columnName, $filtersToApply);
        }

        return $this;
    }

    /**
     * Get array of query parameters that can be used
     *
     * @return array
     */
    protected function filterParameters()
    {
        $filterKeys = array_keys($this->strategy->parameters());

        // get parameters that can be used to filter this query from the current request
        $parameters = collect($this->query)->only($filterKeys)->toArray();

        // find fields that have the operator attached as a suffix
        $otherParameters = collect($this->query)->except($filterKeys)
            ->flatMap(function ($value, $key) {
                $parts = explode('--', $key);
                // skip if there is no operator or is overriding default
                if (count($parts) <= 1 || $this->strategy->parameter($parts[0])->operatorOverride() === $key) {
                    return null;
                }

                if (count($parts) === 2) {
                    return [$parts[0] => [$parts[1] => $value]];
                }
            })
            ->filter()
            ->only($filterKeys)
            ->toArray();

        return collect($parameters)->mergeRecursive($otherParameters)->toArray();
    }

    /**
     * @return mixed[][]
     */
    public function filterValues(): array
    {
        $parameters = $this->filterParameters();
        $filterValues = [];
        foreach ($parameters as $parameter => $values) {
            $parameterConf = $this->strategy->parameter($parameter);
            $filterValues[$parameter] = $this->prepareValues($values, $parameterConf);
        }

        return $filterValues;
    }

    /**
     * Apply order and sorting rules to the query
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
                ->filter(static fn($value, $key): bool => is_int($key))->pop() ?? $defaultDirection;

        $sortBy = $sortValues->filter(static fn($value, $key): bool => !is_int($key));

        $orderBy = [];

        if (is_array($orderValues)) {
            $orderBy = collect($orderValues)->mapWithKeys(static function ($value, $key) use ($sortBy, $defaultDirection) {
                if (is_int($key)) {
                    $direction = $sortBy->get($value) ?? $defaultDirection;
                    return [$value => $direction];
                } elseif (is_array($value)) {
                    return collect($value)->mapWithKeys(static fn($value): array => [$value => $key])->toArray();
                } else {
                    return [$value => $key];
                }
            })->toArray();
        } else {
            $orderBy[strtolower((string) $orderValues)] = strtolower((string) $defaultDirection);
        }

        foreach (collect($orderBy)->only($canOrderBy) as $column => $collection) {
            $direction = (in_array($collection, $directions)) ? $collection : 'asc';
            $this->builder->orderBy($column, $direction);
        }

        return $this;
    }

    /**
     * Limit the amount of results returned
     */
    public function limit(): Filter
    {
        $this->builder->limit($this->getLimitValue());

        return $this;
    }

    /**
     * Paginate the query using the strategy rules
     */
    public function paginate(): Paginated
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
        $lengthAwarePaginator = $this->builder->paginate($perPage);

        $lengthAwarePaginator->appends($appends);

        return new Paginated(
            $lengthAwarePaginator->items(),
            $lengthAwarePaginator->total(),
            $lengthAwarePaginator->perPage(),
            $lengthAwarePaginator->currentPage(), [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $lengthAwarePaginator->getPageName(),
                'appliedFilters' => $this->filterValues(),
            ]
        );
    }

    /**
     * Apply eloquent withs for eager loading relationships
     */
    public function with(): Filter
    {
        $pieces = $this->query[$this->with] ?? [];

        $with = array_filter(explode(',', implode(',', is_array($pieces) ? $pieces : [$pieces])));

        $this->builder->with($with);

        return $this;
    }

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

        return ($limit <= $this->strategy->maxLimit()) ? $limit : $this->strategy->maxLimit();
    }

    private function prepareValues(mixed $values, Parameter $parameter): array
    {
        $filterValues = is_array($values) ? $values : [$values];

        $indexedValues = collect($filterValues)->only(range(0, count($filterValues) - 1))->toArray();

        $namedValues = collect($filterValues)->except(range(0, count($filterValues) - 1))->toArray();

        $indexedValues = $this->transmuteValues($indexedValues, $parameter);
        $namedValues = $this->transmuteValues($namedValues, $parameter);

        $indexedValues = $this->explodeIndexedValues($indexedValues, $parameter);
        $namedValues = $this->explodeNamedValues($namedValues, $parameter);

        $filterValues = array_merge($indexedValues, $namedValues);

        // if there are any disabled filter clauses remove them
        if (!empty($disabled = $parameter->disabled())) {
            // TODO remove need for collect
            $filterValues = collect($filterValues)->except($disabled)->all();
        }

        return $filterValues;
    }

    protected function transmuteValues(array $values, Parameter $parameter)
    {
        if (($transmuteClass = $parameter->transmuteWith()) && (class_exists($transmuteClass) && ($transmute = app($transmuteClass)) instanceof TransmuteInterface)) {
            $values = array_map(static function ($filerValue) use ($transmute) {
                $property = new Property($filerValue);
                $transmute->transmute($property);
                return $property->getValue();
            }, $values);
        }

        return $values;
    }

    protected function explodeIndexedValues(array $values, Parameter $parameter)
    {
        if ($parameter->shouldExplode()) {
            $delimiter = $parameter->explodeDelimiter();
            $values = collect($values)->flatMap(static fn($value): array => array_filter(explode($delimiter, implode($delimiter, is_array($value) ? $value : [$value]))))->toArray();
        }

        return $values;
    }

    protected function explodeNamedValues(array $values, Parameter $parameter)
    {
        if ($parameter->shouldExplode()) {
            $delimiter = $parameter->explodeDelimiter();
            $values = collect($values)->map(static fn($value): array => array_filter(explode($delimiter, implode($delimiter, is_array($value) ? $value : [$value]))))->toArray();
        }

        return $values;
    }

    /**
     * @param $values
     */
    private function findNameValues(array $values): array
    {
        return array_filter($values, static fn($key): bool => !is_int($key), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get the clauses that the parameter can apply to the query
     *
     * @param $parameter
     */
    public function getParameterMethods(string $parameter): array
    {
        $filters = $this->strategy->parameter($parameter)->methods();
        $except = $this->strategy->parameter($parameter)->disabled();
        return array_diff_assoc(array_merge($this->strategy->defaultMethods(), $filters), array_keys($except));
    }

    /**
     * @return array
     */
    private function parameterOverrides()
    {
        $collection = collect($this->strategy->parameters())->map(static fn(Parameter $parameter): string => $parameter->operatorOverride());

        return collect($this->query)->only($collection)->toArray();
    }
}
