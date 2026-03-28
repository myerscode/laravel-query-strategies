<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Iterator;
use Myerscode\Laravel\QueryStrategies\Strategies\StrategyInterface;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;
use Tests\Support\Strategies\OverrideQueryStrategy;
use Tests\Support\Strategies\BasicConfigQueryStrategy;

/**
 * @coversDefaultClass \Myerscode\Laravel\QueryStrategies\Strategies\AbstractStrategy
 */
final class StrategyConfigTest extends TestCase
{

    public static function providerOfStrategies(): Iterator
    {
        yield [new ComplexConfigQueryStrategy];
        yield [new OverrideQueryStrategy];
        yield [new BasicConfigQueryStrategy];
    }

    #[DataProvider('providerOfStrategies')]
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
