<?php

namespace Tests;

use Myerscode\Laravel\QueryStrategies\Exceptions\FilterStrategyNotFoundException;
use Myerscode\Laravel\QueryStrategies\Exceptions\InvalidStrategyException;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;
use Tests\Support\Strategies\OverrideQueryStrategy;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;
use Tests\Support\Strategies\InvalidStrategy;

/**
 * @coversDefaultClass \Myerscode\Laravel\QueryStrategies\StrategyManager
 */
class StrategyManagerTest extends TestCase
{

    public function invalidStrategyExceptionProvider()
    {
        return [
            [InvalidStrategy::class],
            [new InvalidStrategy],
            [new \stdClass()],
        ];
    }

    public function testCanFindStrategy()
    {
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class));
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(OverrideQueryStrategy::class));
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(new ComplexConfigQueryStrategy));
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(new OverrideQueryStrategy));
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(new Strategy()));
    }

    public function testReturnsCachedStrategy()
    {
        $manager = $this->strategyManager();
        $strategy = $manager->findStrategy(ComplexConfigQueryStrategy::class);
        $shouldBeCached = $manager->findStrategy(ComplexConfigQueryStrategy::class);
        $this->assertSame($strategy, $shouldBeCached);
    }

    public function testThrowsFilterStrategyNotFound()
    {
        $this->expectException(FilterStrategyNotFoundException::class);
        $this->strategyManager()->findStrategy('Unknown/Strategy/Class');
    }

    /**
     * @dataProvider invalidStrategyExceptionProvider
     */
    public function testThrowsInvalidStrategyException($possibleStrategy)
    {
        $this->expectException(InvalidStrategyException::class);
        $this->strategyManager()->findStrategy($possibleStrategy);
    }
}
