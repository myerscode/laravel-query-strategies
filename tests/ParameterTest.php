<?php

namespace Tests;

use Iterator;
use Myerscode\Laravel\QueryStrategies\Strategies\Parameter;

/**
 * @coversDefaultClass \Myerscode\Laravel\QueryStrategies\Strategies\Parameter
 */
class ParameterTest extends TestCase
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
            ]
        ];
        yield [
            [
                'filters' => [
                    'does-not-equal' => 'DoesNotEqualClause::class',
                ],
                'disabled' => [
                    'contains',
                ],
            ]
        ];
        yield [
            [
                'override' => 'different-mass-name'
            ]
        ];
        yield [
            [
                'overrideSuffix' => '--mass-filter'
            ]
        ];
        yield [
            [
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testInstanceCreation(array $config): void
    {
        $parameter = new Parameter('foobar', $config);
        $this->assertSame('foobar', $parameter->name());
        $this->assertEquals($config['override'] ?? ('foobar' . ($config['overrideSuffix'] ?? Parameter::DEFAULT_OPERATOR_OVERRIDE_SUFFIX)), $parameter->operatorOverride());
        $this->assertEquals($config['column'] ?? 'foobar', $parameter->column());
        $this->assertEquals($config['default'] ?? null, $parameter->defaultMethod());
        $this->assertEquals($config['methods'] ?? [], $parameter->methods());
        $this->assertEquals($config['disabled'] ?? [], $parameter->disabled());
    }
}
