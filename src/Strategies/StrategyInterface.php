<?php

namespace Myerscode\Laravel\QueryStrategies\Strategies;

interface StrategyInterface
{

    /**
     * Get filter config matrix
     *
     * @return mixed[]
     */
    public function defaultMethods(): array;

    /**
     * Get filter config matrix
     *
     * @return Parameter[]
     */
    public function parameters(): array;

    /**
     * Get filter config matrix
     *
     * @param string $name
     * @return Parameter
     */
    public function parameter(string $name): ?Parameter;

    /**
     * Get the keys that can be used to order the results
     *
     * @return array
     */
    public function canOrderBy(): array;

    /**
     * Get the default limit for number of values to return in a request
     *
     * @return int
     */
    public function limit(): int;

    /**
     * Get the max results value to return in a request
     *
     * @return int
     */
    public function maxLimit(): int;
}
