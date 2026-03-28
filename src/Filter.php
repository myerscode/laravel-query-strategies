<?php

namespace Myerscode\Laravel\QueryStrategies;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Myerscode\Laravel\QueryStrategies\Clause\CallbackClause;
use Myerscode\Laravel\QueryStrategies\Clause\ClauseInterface;
use Myerscode\Laravel\QueryStrategies\Clause\EqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsInClause;
use Myerscode\Laravel\QueryStrategies\Exceptions\InvalidFilterException;
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

    private string $fieldsKey = 'fields';

    private string $limitKey = 'limit';

    private string $orderKey = 'order';

    private string $sortKey = 'sort';

    private bool $strict = false;

    private string $with = 'with';


    /**
     * @param Builder<Model> $builder
     * @param array<string, mixed> $query
     * @param array<string, mixed> $config
     */
    public function __construct(private readonly Builder $builder, private readonly StrategyInterface $strategy, private array $query, array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Apply Filters, Includes, Ordering and Pagination
     *
     * @return LengthAwarePaginator<int, mixed>
     */
    public function apply(): LengthAwarePaginator
    {
        $this->fields();
        $this->filter();
        $this->order();
        $this->limit();
        $this->with();
        return $this->paginate();
    }

    /**
     * Apply a filter clause to a builder
     */
    public function applyFilter(string $class, mixed $value, string $column): Filter
    {
        if (class_exists($class) && ($filter = new $class()) instanceof ClauseInterface) {
            if (str_contains($column, '.')) {
                $parts = explode('.', $column, 2);
                $this->builder->whereHas($parts[0], static function (Builder $builder) use ($filter, $value, $parts): void {
                    $filter->filter($builder, $value, $parts[1]);
                });
            } else {
                $filter->filter($this->builder, $value, $column);
            }
        }

        return $this;
    }

    /**
     * The builder Distill will apply a strategy to
     *
     * @return Builder<Model>
     */
    public function builder(): Builder
    {
        return $this->builder;
    }

    /**
     * Select specific fields on the query
     */
    public function fields(): Filter
    {
        $pieces = $this->query[$this->fieldsKey] ?? [];

        $requested = array_filter(explode(',', implode(',', is_array($pieces) ? $pieces : [$pieces])));

        if ($requested === []) {
            return $this;
        }

        $allowed = $this->strategy->allowedFields();

        if ($allowed !== []) {
            $requested = array_intersect($requested, $allowed);
        }

        if ($requested !== []) {
            $this->builder->select($requested);
        }

        return $this;
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

            if (!$parameterConf instanceof Parameter) {
                continue;
            }

            // Handle callback filters directly
            if ($parameterConf->callback() instanceof Closure) {
                $callbackClause = new CallbackClause($parameterConf->callback());
                $columnName = $parameterConf->column() ?? $parameter;
                $callbackClause->filter($this->builder, $values, $columnName);
                continue;
            }

            $filterValues = $this->prepareValues($values, $parameterConf);

            $methods = $this->getParameterMethods($parameter);

            $namedFilters = $this->findNameValues($filterValues);

            $defaultFilters = array_diff_key($filterValues, $namedFilters);

            $defaultFilter = $parameterConf->defaultMethod();

            if (in_array($defaultFilter, [null, '', '0'], true)) {
                $defaultFilter = $this->defaultFilter;
            }

            if (count($defaultFilters) > 1) {
                $defaultFilter = $parameterConf->multiMethod();
                if (in_array($defaultFilter, [null, '', '0'], true)) {
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

            $columnName = $parameterConf->column() ?? $parameter;

            $this->applyFilters($columnName, $filtersToApply);
        }

        return $this;
    }

    /**
     * @return array<string, array<int|string, mixed>>
     */
    public function filterValues(): array
    {
        $parameters = $this->filterParameters();
        $filterValues = [];
        foreach ($parameters as $parameter => $values) {
            $parameterConf = $this->strategy->parameter($parameter);

            if (!$parameterConf instanceof Parameter) {
                continue;
            }

            $filterValues[$parameter] = $this->prepareValues($values, $parameterConf);
        }

        return $filterValues;
    }

    /**
     * Get the clauses that the parameter can apply to the query
     *
     * @return array<string, string>
     */
    public function getParameterMethods(string $parameter): array
    {
        $parameterConf = $this->strategy->parameter($parameter);

        if (!$parameterConf instanceof Parameter) {
            return $this->strategy->defaultMethods();
        }

        $filters = $parameterConf->methods();
        $except = $parameterConf->disabled();
        return array_diff_assoc(array_merge($this->strategy->defaultMethods(), $filters), array_keys($except));
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

        $sortRaw = $this->query[$sortKey] ?? $defaultDirection;
        $sortArray = is_array($sortRaw) ? $sortRaw : [$sortRaw];

        $sortBy = [];
        foreach ($sortArray as $key => $value) {
            if (is_int($key)) {
                $defaultDirection = $value;
            } else {
                $sortBy[$key] = $value;
            }
        }

        $orderBy = [];

        if (is_array($orderValues)) {
            foreach ($orderValues as $key => $value) {
                if (is_int($key)) {
                    $orderBy[$value] = $sortBy[$value] ?? $defaultDirection;
                } elseif (is_array($value)) {
                    foreach ($value as $v) {
                        $orderBy[$v] = $key;
                    }
                } else {
                    $orderBy[$value] = $key;
                }
            }
        } else {
            $orderBy[strtolower((string) $orderValues)] = strtolower((string) $defaultDirection);
        }

        $allowedOrderBy = array_intersect_key($orderBy, array_flip($canOrderBy));

        foreach ($allowedOrderBy as $column => $direction) {
            $direction = (in_array($direction, $directions)) ? $direction : 'asc';
            $this->builder->orderBy($column, $direction);
        }

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

        $lengthAwarePaginator = $this->builder->paginate($perPage);

        $lengthAwarePaginator->appends($appends);

        return new Paginated(
            $lengthAwarePaginator->items(),
            $lengthAwarePaginator->total(),
            $lengthAwarePaginator->perPage(),
            $lengthAwarePaginator->currentPage(),
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $lengthAwarePaginator->getPageName(),
                'appliedFilters' => $this->filterValues(),
            ],
        );
    }

    /**
     * Apply eloquent withs for eager loading relationships
     */
    public function with(): Filter
    {
        $pieces = $this->query[$this->with] ?? [];

        $requested = array_filter(explode(',', implode(',', is_array($pieces) ? $pieces : [$pieces])));

        $aggregates = $this->strategy->aggregateIncludes();
        $allowed = $this->strategy->canWith();

        $eagerLoads = [];
        foreach ($requested as $include) {
            if (isset($aggregates[$include])) {
                $agg = $aggregates[$include];
                match ($agg['type']) {
                    'count' => $this->builder->withCount($agg['relationship']),
                    'exists' => $this->builder->withExists($agg['relationship']),
                    'sum' => $this->builder->withSum($agg['relationship'], $agg['column'] ?? ''),
                    'avg' => $this->builder->withAvg($agg['relationship'], $agg['column'] ?? ''),
                    'min' => $this->builder->withMin($agg['relationship'], $agg['column'] ?? ''),
                    'max' => $this->builder->withMax($agg['relationship'], $agg['column'] ?? ''),
                    default => null,
                };
            } else {
                $eagerLoads[] = $include;
            }
        }

        if ($allowed !== []) {
            $eagerLoads = array_intersect($eagerLoads, $allowed);
        }

        $this->builder->with($eagerLoads);

        return $this;
    }

    /**
     * @param array<string, array<int|string, mixed>> $filters
     */
    protected function applyFilters(string $column, array $filters): void
    {
        foreach ($filters as $filterClass => $filterValues) {
            $this->applyFilter($filterClass, $filterValues, $column);
        }
    }

    /**
     * @param array<int|string, mixed> $values
     * @return array<int|string, mixed>
     */
    protected function explodeIndexedValues(array $values, Parameter $parameter): array
    {
        if ($parameter->shouldExplode()) {
            $delimiter = $parameter->explodeDelimiter();
            if ($delimiter !== '') {
                $exploded = [];
                foreach ($values as $value) {
                    $parts = array_filter(explode($delimiter, implode($delimiter, is_array($value) ? $value : [$value])));
                    array_push($exploded, ...$parts);
                }

                $values = $exploded;
            }
        }

        return $values;
    }

    /**
     * @param array<int|string, mixed> $values
     * @return array<int|string, mixed>
     */
    protected function explodeNamedValues(array $values, Parameter $parameter): array
    {
        if ($parameter->shouldExplode()) {
            $delimiter = $parameter->explodeDelimiter();
            if ($delimiter !== '') {
                $values = array_map(static fn ($value): array => array_filter(explode($delimiter, implode($delimiter, is_array($value) ? $value : [$value]))), $values);
            }
        }

        return $values;
    }

    /**
     * Get array of query parameters that can be used
     *
     * @return array<string, mixed>
     */
    protected function filterParameters(): array
    {
        $filterKeys = array_keys($this->strategy->parameters());

        // In strict mode, throw if unknown filter parameters are present
        if ($this->strict) {
            $systemKeys = [$this->fieldsKey, $this->orderKey, $this->sortKey, $this->limitKey, $this->with, 'page'];
            $allowedKeys = array_merge($filterKeys, $systemKeys);

            // Also allow operator overrides and suffixed operators
            foreach (array_keys($this->query) as $key) {
                $keyStr = (string) $key;
                if (in_array($keyStr, $allowedKeys, true)) {
                    continue;
                }

                // Check if it's an operator override or suffixed operator
                $parts = explode('--', $keyStr);
                if (count($parts) === 2 && in_array($parts[0], $filterKeys, true)) {
                    continue;
                }

                $parameterConf = $this->strategy->parameter($parts[0]);
                if ($parameterConf instanceof Parameter && $parameterConf->operatorOverride() === $keyStr) {
                    continue;
                }

                throw new InvalidFilterException(
                    sprintf("Filter '%s' is not allowed. Allowed filters: ", $keyStr) . implode(', ', $filterKeys)
                );
            }
        }

        // get parameters that can be used to filter this query from the current request
        $parameters = array_intersect_key($this->query, array_flip($filterKeys));

        // find fields that have the operator attached as a suffix
        $exceptQuery = array_diff_key($this->query, array_flip($filterKeys));
        $otherParameters = [];
        foreach ($exceptQuery as $key => $value) {
            $parts = explode('--', (string) $key);
            $parameterConf = $this->strategy->parameter($parts[0]);
            if (count($parts) <= 1) {
                continue;
            }
            if ($parameterConf instanceof Parameter && $parameterConf->operatorOverride() === $key) {
                continue;
            }

            if (count($parts) === 2 && in_array($parts[0], $filterKeys, true)) {
                $otherParameters[$parts[0]][$parts[1]] = $value;
            }
        }

        $merged = array_merge_recursive($parameters, $otherParameters);

        // Apply default values for parameters not present in the request
        foreach ($this->strategy->parameters() as $name => $parameterConf) {
            if (!isset($merged[$name]) && $parameterConf->hasDefaultValue() && $parameterConf->defaultValue() !== null) {
                $merged[$name] = $parameterConf->defaultValue();
            }
        }

        return $merged;
    }

    /**
     * @param array<int|string, mixed> $values
     * @return array<int|string, mixed>
     */
    protected function transmuteValues(array $values, Parameter $parameter): array
    {
        if (($transmuteClass = $parameter->transmuteWith()) && (class_exists($transmuteClass) && ($transmute = new $transmuteClass()) instanceof TransmuteInterface)) {
            return array_map(static function ($filerValue) use ($transmute) {
                $property = new Property($filerValue);
                $transmute->transmute($property);
                return $property->getValue();
            }, $values);
        }

        return $values;
    }

    /**
     * @param array<int|string, mixed> $values
     * @return array<string, mixed>
     */
    private function findNameValues(array $values): array
    {
        return array_filter($values, static fn ($key): bool => !is_int($key), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get the limit value for restricting result count
     */
    private function getLimitValue(): int
    {
        $limitKey = $this->limitKey;

        /**
         * Get the number of items to return for the query
         */
        $limit = $this->query[$limitKey] ?? $this->strategy->limit();

        if (!is_numeric($limit) || $limit < 0) {
            $limit = $this->strategy->limit();
        }

        $limit = (int) $limit;

        return ($limit <= $this->strategy->maxLimit()) ? $limit : $this->strategy->maxLimit();
    }

    /**
     * @return array<string, mixed>
     */
    private function parameterOverrides(): array
    {
        $overrideKeys = [];
        foreach ($this->strategy->parameters() as $parameter) {
            $overrideKeys[] = $parameter->operatorOverride();
        }

        return array_intersect_key($this->query, array_flip($overrideKeys));
    }

    /**
     * @return array<int|string, mixed>
     */
    private function prepareValues(mixed $values, Parameter $parameter): array
    {
        $filterValues = is_array($values) ? $values : [$values];

        $indexedValues = [];
        $namedValues = [];
        foreach ($filterValues as $key => $value) {
            if (is_int($key)) {
                $indexedValues[$key] = $value;
            } else {
                $namedValues[$key] = $value;
            }
        }

        $indexedValues = $this->transmuteValues($indexedValues, $parameter);
        $namedValues = $this->transmuteValues($namedValues, $parameter);

        $indexedValues = $this->explodeIndexedValues($indexedValues, $parameter);
        $namedValues = $this->explodeNamedValues($namedValues, $parameter);

        $filterValues = array_merge($indexedValues, $namedValues);

        // Remove ignored values
        if (($ignored = $parameter->ignoredValues()) !== []) {
            $filterValues = array_filter($filterValues, static fn ($v): bool => !in_array($v, $ignored, true));
            // Re-index numeric keys
            $reindexed = [];
            foreach ($filterValues as $key => $value) {
                if (is_int($key)) {
                    $reindexed[] = $value;
                } else {
                    $reindexed[$key] = $value;
                }
            }

            $filterValues = $reindexed;
        }

        // if there are any disabled filter clauses remove them
        if (($disabled = $parameter->disabled()) !== []) {
            return array_diff_key($filterValues, array_flip($disabled));
        }

        return $filterValues;
    }

    /**
     * Set any configurable options
     *
     * @param array<string, mixed> $config
     */
    private function setConfig(array $config): void
    {
        $this->strict = (bool) ($config['strict'] ?? false);
        $this->fieldsKey = $config['fields'] ?? 'fields';
        $this->orderKey = $config['order'] ?? 'order';
        $this->sortKey = $config['sort'] ?? 'sort';
        $this->limitKey = $config['limit'] ?? 'limit';
        $this->with = $config['with'] ?? 'with';
    }
}
