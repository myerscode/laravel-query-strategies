<?php

namespace Tests;

use Myerscode\Laravel\QueryStrategies\Strategies\Parameter;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;
use Tests\Support\Strategies\OverrideQueryStrategy;
use Tests\Support\Strategies\BasicConfigQueryStrategy;

/**
 * @coversDefaultClass \Myerscode\Laravel\QueryStrategies\Strategies\AbstractStrategy
 */
class StrategyTest extends TestCase
{

    public function providerOfStrategies()
    {
        return [
            [ComplexConfigQueryStrategy::class],
            [OverrideQueryStrategy::class],
            [BasicConfigQueryStrategy::class],
        ];
    }

    /**
     * @dataProvider providerOfStrategies
     */
    public function testReturnsProperties($strategy)
    {
        /**
         * @var $strategy StrategyInterface
         */
        $strategy = new $strategy;
        $this->assertInternalType('array', $strategy->defaultMethods());
        $this->assertInternalType('array', $strategy->parameters());
        $this->assertInternalType('int', $strategy->limit());
        $this->assertInternalType('int', $strategy->maxLimit());
        $this->assertInternalType('array', $strategy->canOrderBy());
    }
}
