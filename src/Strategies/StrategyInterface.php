<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

interface StrategyInterface
{
    /**
     * Get the keys that can be used to order the results
     */
    public function canOrderBy(): array;

    /**
     * Get filter config matrix
     *
     * @return mixed[]
     */
    public function defaultMethods(): array;

    /**
     * Get the default limit for number of values to return in a request
     */
    public function limit(): int;

    /**
     * Get the max results value to return in a request
     */
    public function maxLimit(): int;

    /**
     * Get filter config matrix
     *
     * @return Parameter
     */
    public function parameter(string $name): ?Parameter;

    /**
     * Get filter config matrix
     *
     * @return Parameter[]
     */
    public function parameters(): array;
}
