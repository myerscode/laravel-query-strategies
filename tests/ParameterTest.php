<?php

namespace Tests;

use Myerscode\Laravel\QueryStrategies\Strategies\Parameter;

/**
 * @coversDefaultClass \Myerscode\Laravel\QueryStrategies\Strategies\Parameter
 */
class ParameterTest extends TestCase
{

    public function dataProvider()
    {
        return [
            [
                [
                    'column' => 'foobar',
                    'default' => 'FooBar::class',
                    'filters' => [
                        'does-not-equal' => 'HelloWorld::class',
                    ],
                ]
            ],
            [
                [
                    'filters' => [
                        'does-not-equal' => 'DoesNotEqualClause::class',
                    ],
                    'disabled' => [
                        'contains',
                    ],
                ]
            ],
            [
                [
                    'override' => 'different-mass-name'
                ]
            ],
            [
                [
                    'overrideSuffix' => '--mass-filter'
                ]
            ],
            [
                [
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testInstanceCreation($config)
    {
        $parameter = new Parameter('foobar', $config);
        $this->assertEquals('foobar', $parameter->name());
        $this->assertEquals($config['override'] ?? ('foobar' . ($config['overrideSuffix'] ?? Parameter::DEFAULT_OPERATOR_OVERRIDE_SUFFIX)), $parameter->operatorOverride());
        $this->assertEquals($config['column'] ?? 'foobar', $parameter->column());
        $this->assertEquals($config['default'] ?? null, $parameter->defaultMethod());
        $this->assertEquals($config['methods'] ?? [], $parameter->methods());
        $this->assertEquals($config['disabled'] ?? [], $parameter->disabled());
    }
}
