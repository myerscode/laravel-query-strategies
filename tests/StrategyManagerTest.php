<?php

declare(strict_types=1);

namespace Tests;

use Iterator;
use stdClass;
use TypeError;
use Myerscode\Laravel\QueryStrategies\Exceptions\FilterStrategyNotFoundException;
use Myerscode\Laravel\QueryStrategies\Exceptions\InvalidStrategyException;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;
use Myerscode\Laravel\QueryStrategies\StrategyManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Models\Item;
use Tests\Support\Strategies\OverrideQueryStrategy;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;
use Tests\Support\Strategies\InvalidStrategy;

#[CoversClass(StrategyManager::class)]
final class StrategyManagerTest extends TestCase
{
    public static function invalidStrategyExceptionProvider(): Iterator
    {
        yield [InvalidStrategy::class];
    }

    public static function invalidStrategyTypeErrorProvider(): Iterator
    {
        yield [new InvalidStrategy()];
        yield [new stdClass()];
    }

    public function test_can_find_strategy(): void
    {
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class));
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(OverrideQueryStrategy::class));
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(new ComplexConfigQueryStrategy()));
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(new OverrideQueryStrategy()));
        $this->assertInstanceOf(StrategyInterface::class, $this->strategyManager()->findStrategy(new Strategy()));
    }

    public function test_returns_cached_strategy(): void
    {
        $strategyManager = $this->strategyManager();
        $strategy = $strategyManager->findStrategy(ComplexConfigQueryStrategy::class);
        $shouldBeCached = $strategyManager->findStrategy(ComplexConfigQueryStrategy::class);
        $this->assertSame($strategy, $shouldBeCached);
    }

    public function test_throws_filter_strategy_not_found(): void
    {
        $this->expectException(FilterStrategyNotFoundException::class);
        $this->strategyManager()->findStrategy('Unknown/Strategy/Class');
    }

    #[DataProvider('invalidStrategyExceptionProvider')]
    public function test_throws_invalid_strategy_exception(string $possibleStrategy): void
    {
        $this->expectException(InvalidStrategyException::class);
        $this->strategyManager()->findStrategy($possibleStrategy);
    }

    public function test_throws_invalid_strategy_for_model(): void
    {
        $this->expectException(InvalidStrategyException::class);
        $this->strategyManager()->findStrategy(new Item());
    }

    #[DataProvider('invalidStrategyTypeErrorProvider')]
    public function test_throws_type_error_for_invalid_objects(object $possibleStrategy): void
    {
        $this->expectException(TypeError::class);
        $this->strategyManager()->findStrategy($possibleStrategy);
    }
}
