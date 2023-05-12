<?php

namespace Tests;

use Myerscode\Laravel\QueryStrategies\Clause\BeginsWithClause;
use Myerscode\Laravel\QueryStrategies\Clause\ContainsClause;
use Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause;
use Myerscode\Laravel\QueryStrategies\Clause\EndsWithClause;
use Myerscode\Laravel\QueryStrategies\Clause\EqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\GreaterThanClause;
use Myerscode\Laravel\QueryStrategies\Clause\GreaterThanOrEqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsInClause;
use Myerscode\Laravel\QueryStrategies\Clause\IsNotInClause;
use Myerscode\Laravel\QueryStrategies\Clause\LessThanClause;
use Myerscode\Laravel\QueryStrategies\Clause\LessThanOrEqualsClause;
use Myerscode\Laravel\QueryStrategies\Clause\OrEqualsClause;
use Tests\Support\Models\Item;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;

class ClauseTest extends TestCase
{

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\BeginsWithClause
     */
    public function testBeginsWithFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(BeginsWithClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "like",
                "value" => "foobar%",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\ContainsClause
     */
    public function testContainsFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(ContainsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "like",
                "value" => "%foobar%",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause
     */
    public function testDoesNotEqualFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(DoesNotEqualClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "!=",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\EndsWithClause
     */
    public function testEndsWithFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(EndsWithClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "like",
                "value" => "%foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\EqualsClause
     */
    public function testEqualsFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(EqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "=",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\GreaterThanClause
     */
    public function testGreaterThanFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(GreaterThanClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => ">",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\GreaterThanOrEqualsClause
     */
    public function testGreaterThanOrEqualsFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(GreaterThanOrEqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => ">=",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\IsInClause
     */
    public function testIsInFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(IsInClause::class, ['foo', 'bar'], 'test_column');

        $where = [
            [
                'type' => 'In',
                'column' => 'test_column',
                'boolean' => 'and',
                "values" => ['foo', 'bar'],
            ],
        ];
        $this->assertEquals($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\IsNotInClause
     */
    public function testIsNotInFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(IsNotInClause::class, ['foo', 'bar'], 'test_column');

        $where = [
            [
                'type' => 'NotIn',
                'column' => 'test_column',
                'boolean' => 'and',
                "values" => ['foo', 'bar'],
            ],
        ];
        $this->assertEquals($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\LessThanClause
     */
    public function testLessThanFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(LessThanClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "<",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\LessThanOrEqualsClause
     */
    public function testLessThanOrEqualsFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(LessThanOrEqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "<=",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\OrEqualsClause
     */
    public function testOrEqualsFilterClause(): void
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(OrEqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "=",
                "value" => "foobar",
                "boolean" => "or",
            ],
        ];
        $this->assertSame($where, $distill->builder()->getQuery()->wheres);
    }
}
