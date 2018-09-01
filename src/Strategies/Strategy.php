<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

use Myerscode\Laravel\QueryStrategies\Clause\BeginsWithClause;
use Myerscode\Laravel\QueryStrategies\Clause\ContainsClause;
use Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause;
use Myerscode\Laravel\QueryStrategies\Clause\EndsWithClause;
use Myerscode\Laravel\QueryStrategies\Clause\EqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\GreaterThanClause;
use Myerscode\Laravel\QueryStrategies\Clause\GreaterThanOrEqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsInClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsNotInClause;
use Myerscode\Laravel\QueryStrategies\Clause\LessThanClause;
use Myerscode\Laravel\QueryStrategies\Clause\LessThanOrEqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\OrEqualsClause;

class Strategy implements StrategyInterface
{

    /**
     * Parameter config
     *
     * @var []
     */
    protected $config = [
        //
    ];

    /**
     * Parameters which can be applied a query
     *
     * @var Parameter[]
     */
    protected $parameters = [
        //
    ];

    /**
     * Supported default filter methods
     *
     * @var array
     */
    protected $defaultMethods = [
        BeginsWithClause::class => ['beginsWith', '*%'],
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
        OrEqualsClause::class => ['or', '||'],
    ];

    /**
     * How many records should be returned by default
     *
     * @var int
     */
    protected $limitTo = 50;

    /**
     * Maximum number of records a api response can have
     *
     * @var int
     */
    protected $maxLimit = 150;

    /**
     * The model which to apply this strategy to
     *
     * @var []
     */
    protected $canOrderBy = [
        'id',
    ];

    public function __construct()
    {
        $this->compile();
    }

    /**
     * Compile the policy config into parameters
     */
    private function compile()
    {
        $parameters = $this->config() ?? [];

        foreach ($parameters as $parameter => $config) {
            if (isset($config['aliases']) && is_array($config['aliases'])) {
                array_walk($config['aliases'], function ($alias) use (&$parameters, $config) {
                    $parameters[$alias] = $config;
                });
            }
        }

        foreach ($parameters as $parameter => $config) {
            if (is_int($parameter) && is_string($config)) {
                $name = $config;
                $parameter = new Parameter($config, []);
            } else {
                $name = $parameter;
                $parameter = new Parameter($parameter, $config);
            }
            $this->setParameter($name, $parameter);
        }
    }

    /**
     * Set a compiled filter parameter config
     *
     * @param string $name
     * @param Parameter $parameter
     * @return self
     */
    private function setParameter(string $name, Parameter $parameter): self
    {
        $this->parameters[$name] = $parameter;

        return $this;
    }

    /**
     * Get the compiled filter parameter config
     *
     * @param string $parameter
     * @return Parameter
     */
    public function parameter(string $parameter): ?Parameter
    {
        return $this->parameters[$parameter] ?? null;
    }

    /**
     * Get collection of default methods
     *
     * @return string[]
     */
    public function defaultMethods(): array
    {
        $defaultMethods = [];
        foreach ($this->defaultMethods as $class => $aliases) {
            foreach ($aliases as $alias) {
                $defaultMethods[$alias] = $class;
            }
        }
        return $defaultMethods;
    }

    /**
     * Get cofig for filter keys and validators to be applied to them
     *
     * @return string[]
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * Get collection of filter keys and validators to be applied to them
     *
     * @return string[]
     */
    public function parameters(): array
    {
        return $this->parameters;
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
     * {@inheritdoc}
     */
    public function canOrderBy(): array
    {
        return $this->canOrderBy;
    }
}
