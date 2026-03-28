<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\BeginsWithClause;
use Myerscode\Laravel\QueryStrategies\Clause\BetweenClause;
use Myerscode\Laravel\QueryStrategies\Clause\ContainsClause;
use Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause;
use Myerscode\Laravel\QueryStrategies\Clause\EndsWithClause;
use Myerscode\Laravel\QueryStrategies\Clause\EqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\GreaterThanClause;
use Myerscode\Laravel\QueryStrategies\Clause\GreaterThanOrEqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsInClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsNotInClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsNotNullClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsNullClause;
use Myerscode\Laravel\QueryStrategies\Clause\LessThanClause;
use Myerscode\Laravel\QueryStrategies\Clause\LessThanOrEqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\OrEqualsClause;

class Strategy implements StrategyInterface
{
    /**
     * Aggregate includes configuration
     *
     * @var array<string, array{type: string, relationship: string, column?: string}>
     */
    protected array $aggregateIncludes = [];
    /**
     * Columns that can be selected via field selection
     *
     * @var array<int, string>
     */
    protected array $allowedFields = [];

    /**
     * The model which to apply this strategy to
     *
     * @var array<int, string>
     */
    protected array $canOrderBy = [
        'id',
    ];

    /**
     * Relationships that can be eager loaded
     *
     * @var array<int, string>
     */
    protected array $canWith = [];

    /**
     * Parameter config
     *
     * @var array<int|string, array<string, mixed>|string>
     */
    protected array $config = [
        //
    ];

    /**
     * Supported default filter methods
     *
     * @var array<class-string, array<int, string>>
     */
    protected array $defaultMethods = [
        BeginsWithClause::class => ['beginsWith', '*%'],
        BetweenClause::class => ['between', '><'],
        ContainsClause::class => ['contains', '%%'],
        EndsWithClause::class => ['endsWith', '%*'],
        LessThanClause::class => ['lessThan', '<', 'lt'],
        LessThanOrEqualsClause::class => ['lessThanOrEquals', '<=', 'lte'],
        GreaterThanClause::class => ['greaterThan', '>', 'gt'],
        GreaterThanOrEqualsClause::class => ['greaterThanOrEquals', '>=', 'gte'],
        EqualsClause::class => ['is', '='],
        DoesNotEqualClause::class => ['not', '!'],
        IsInClause::class => ['isIn', 'in'],
        IsNotInClause::class => ['notIn', '!in'],
        IsNullClause::class => ['isNull', 'null'],
        IsNotNullClause::class => ['isNotNull', '!null', 'notNull'],
        OrEqualsClause::class => ['or', '||'],
    ];

    /**
     * How many records should be returned by default
     */
    protected int $limitTo = 50;

    /**
     * Maximum number of records a api response can have
     */
    protected int $maxLimit = 150;

    /**
     * Parameters which can be applied a query
     *
     * @var array<string, Parameter>
     */
    protected array $parameters = [
        //
    ];

    /**
     * Compiled alias-to-class map for default methods
     *
     * @var array<string, string>
     */
    private array $compiledDefaultMethods = [];

    public function __construct()
    {
        $this->compile();
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateIncludes(): array
    {
        return $this->aggregateIncludes;
    }

    /**
     * {@inheritdoc}
     */
    public function allowedFields(): array
    {
        return $this->allowedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function canOrderBy(): array
    {
        return $this->canOrderBy;
    }

    /**
     * {@inheritdoc}
     */
    public function canWith(): array
    {
        return $this->canWith;
    }

    /**
     * Get cofig for filter keys and validators to be applied to them
     *
     * @return array<int|string, array<string, mixed>|string>
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * Get collection of default methods
     *
     * @return array<string, string>
     */
    public function defaultMethods(): array
    {
        if ($this->compiledDefaultMethods === []) {
            foreach ($this->defaultMethods as $class => $aliases) {
                foreach ($aliases as $alias) {
                    $this->compiledDefaultMethods[$alias] = $class;
                }
            }
        }

        return $this->compiledDefaultMethods;
    }

    /**
     * {@inheritdoc}
     */
    public function limit(): int
    {
        return $this->limitTo;
    }

    /**
     * {@inheritdoc}
     */
    public function maxLimit(): int
    {
        return $this->maxLimit;
    }

    /**
     * Get the compiled filter parameter config
     */
    public function parameter(string $parameter): ?Parameter
    {
        return $this->parameters[$parameter] ?? null;
    }

    /**
     * Get collection of filter keys and validators to be applied to them
     *
     * @return array<string, Parameter>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * Compile the policy config into parameters
     */
    private function compile(): void
    {
        /** @var array<int|string, array<string, mixed>|string> $parameters */
        $parameters = $this->config();

        foreach ($parameters as $config) {
            if (is_array($config) && isset($config['aliases']) && is_array($config['aliases'])) {
                array_walk($config['aliases'], static function ($alias) use (&$parameters, $config): void {
                    $parameters[$alias] = $config;
                });
            }
        }

        foreach ($parameters as $parameter => $config) {
            if (is_int($parameter) && is_string($config)) {
                $name = $config;
                $parameter = new Parameter($config, []);
            } else {
                $name = (string) $parameter;
                /** @var array<string, mixed> $config */
                $parameter = new Parameter($name, $config);
            }

            $this->setParameter($name, $parameter);
        }
    }

    /**
     * Set a compiled filter parameter config
     */
    private function setParameter(string $name, Parameter $parameter): self
    {
        $this->parameters[$name] = $parameter;

        return $this;
    }
}
