<?php

namespace Tests;

use Iterator;
use Myerscode\Laravel\QueryStrategies\Strategies\Parameter;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;
use Tests\Support\Strategies\OverrideQueryStrategy;
use Tests\Support\Strategies\BasicConfigQueryStrategy;

/**
 * @coversDefaultClass \Myerscode\Laravel\QueryStrategies\Strategies\AbstractStrategy
 */
class StrategyConfigTest extends TestCase
{

    public static function providerOfStrategies(): Iterator
    {
        yield [new ComplexConfigQueryStrategy];
        yield [new OverrideQueryStrategy];
        yield [new BasicConfigQueryStrategy];
    }

    /**
     * @dataProvider providerOfStrategies
     */
    public function testReturnsProperties(StrategyInterface $strategy): void
    {
        $strategy = new $strategy;
        $this->assertIsArray($strategy->defaultMethods());
        $this->assertIsArray($strategy->parameters());
        $this->assertIsInt($strategy->limit());
        $this->assertIsInt($strategy->maxLimit());
        $this->assertIsArray($strategy->canOrderBy());
    }
}
