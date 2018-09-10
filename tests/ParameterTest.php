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
        $this->assertEquals('foobar', $parameter->getName());
        $this->assertEquals($config['override'] ?? ('foobar' . ($config['overrideSuffix'] ?? Parameter::DEFAULT_OVERRIDE_SUFFIX)), $parameter->getOverride());
        $this->assertEquals($config['column'] ?? 'foobar', $parameter->getColumn());
        $this->assertEquals($config['default'] ?? null, $parameter->getDefault());
        $this->assertEquals($config['methods'] ?? [], $parameter->getMethods());
        $this->assertEquals($config['disabled'] ?? [], $parameter->getDisabled());
    }
}
