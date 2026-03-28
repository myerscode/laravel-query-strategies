<?php

declare(strict_types=1);

namespace Tests;

use Iterator;
use Myerscode\Laravel\QueryStrategies\Strategies\Strategy;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;
use Tests\Support\Strategies\OverrideQueryStrategy;
use Tests\Support\Strategies\BasicConfigQueryStrategy;

#[CoversClass(Strategy::class)]
final class StrategyConfigTest extends TestCase
{
    public static function providerOfStrategies(): Iterator
    {
        yield [new ComplexConfigQueryStrategy()];
        yield [new OverrideQueryStrategy()];
        yield [new BasicConfigQueryStrategy()];
    }

    #[DataProvider('providerOfStrategies')]
    public function test_returns_properties(StrategyInterface $strategy): void
    {
        $strategy = new $strategy();
        $this->assertIsArray($strategy->defaultMethods());
        $this->assertIsArray($strategy->parameters());
        $this->assertIsInt($strategy->limit());
        $this->assertIsInt($strategy->maxLimit());
        $this->assertIsArray($strategy->canOrderBy());
    }
}
