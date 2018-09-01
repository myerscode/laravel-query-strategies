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
        $this->assertEquals('foobar--filter', $parameter->getMassFilter());
        $this->assertEquals($config[Parameter::COLUMN_KEY] ?? 'foobar', $parameter->getColumn());
        $this->assertEquals($config[Parameter::DEFAULT_KEY] ?? null, $parameter->getDefault());
        $this->assertEquals($config[Parameter::METHODS_KEY] ?? [], $parameter->getMethods());
        $this->assertEquals($config[Parameter::DISABLED_KEY] ?? [], $parameter->getDisabled());
    }
}
