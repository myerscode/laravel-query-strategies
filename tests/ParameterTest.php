<?php

declare(strict_types=1);

namespace Tests;

use Iterator;
use Myerscode\Laravel\QueryStrategies\Strategies\Parameter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Parameter::class)]
final class ParameterTest extends TestCase
{
    public static function dataProvider(): Iterator
    {
        yield [
            [
                'column' => 'foobar',
                'default' => 'FooBar::class',
                'filters' => [
                    'does-not-equal' => 'HelloWorld::class',
                ],
            ],
        ];
        yield [
            [
                'filters' => [
                    'does-not-equal' => 'DoesNotEqualClause::class',
                ],
                'disabled' => [
                    'contains',
                ],
            ],
        ];
        yield [
            [
                'override' => 'different-mass-name',
            ],
        ];
        yield [
            [
                'overrideSuffix' => '--mass-filter',
            ],
        ];
        yield [
            [
            ],
        ];
    }

    public function test_explode_delimiter_returns_custom(): void
    {
        $parameter = new Parameter('test', ['explode' => true, 'delimiter' => '||']);
        $this->assertSame('||', $parameter->explodeDelimiter());
        $this->assertTrue($parameter->shouldExplode());
    }

    public function test_explode_delimiter_returns_default(): void
    {
        $parameter = new Parameter('test', []);
        $this->assertSame(Parameter::DEFAULT_EXPLODE_DELIMITER, $parameter->explodeDelimiter());
        $this->assertFalse($parameter->shouldExplode());
    }

    #[DataProvider('dataProvider')]
    public function test_instance_creation(array $config): void
    {
        $parameter = new Parameter('foobar', $config);
        $this->assertSame('foobar', $parameter->name());
        $this->assertEquals($config['override'] ?? ('foobar' . ($config['overrideSuffix'] ?? Parameter::DEFAULT_OPERATOR_OVERRIDE_SUFFIX)), $parameter->operatorOverride());
        $this->assertEquals($config['column'] ?? 'foobar', $parameter->column());
        $this->assertEquals($config['default'] ?? null, $parameter->defaultMethod());
        $this->assertEquals($config['methods'] ?? [], $parameter->methods());
        $this->assertEquals($config['disabled'] ?? [], $parameter->disabled());
    }

    public function test_multi_method_returns_null_by_default(): void
    {
        $parameter = new Parameter('test', []);
        $this->assertNull($parameter->multiMethod());
    }

    public function test_transmute_with_returns_null_by_default(): void
    {
        $parameter = new Parameter('test', []);
        $this->assertNull($parameter->transmuteWith());
    }
}
