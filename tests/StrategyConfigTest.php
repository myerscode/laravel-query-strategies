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
            [new ComplexConfigQueryStrategy],
            [new OverrideQueryStrategy],
            [new BasicConfigQueryStrategy],
        ];
    }

    /**
     * @dataProvider providerOfStrategies
     */
    public function testReturnsProperties(StrategyInterface $strategy)
    {
        $strategy = new $strategy;
        $this->assertIsArray($strategy->defaultMethods());
        $this->assertIsArray($strategy->parameters());
        $this->assertIsInt($strategy->limit());
        $this->assertIsInt($strategy->maxLimit());
        $this->assertIsArray($strategy->canOrderBy());
    }
}
