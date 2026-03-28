<?php

declare(strict_types=1);

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

final class ClauseTest extends TestCase
{

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\BeginsWithClause
     */
    public function testBeginsWithFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(BeginsWithClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "like",
                "value" => "foobar%",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\ContainsClause
     */
    public function testContainsFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(ContainsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "like",
                "value" => "%foobar%",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\DoesNotEqualClause
     */
    public function testDoesNotEqualFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(DoesNotEqualClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "!=",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\EndsWithClause
     */
    public function testEndsWithFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(EndsWithClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "like",
                "value" => "%foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\EqualsClause
     */
    public function testEqualsFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(EqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "=",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\GreaterThanClause
     */
    public function testGreaterThanFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(GreaterThanClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => ">",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\GreaterThanOrEqualsClause
     */
    public function testGreaterThanOrEqualsFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(GreaterThanOrEqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => ">=",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\IsInClause
     */
    public function testIsInFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(IsInClause::class, ['foo', 'bar'], 'test_column');

        $where = [
            [
                'type' => 'In',
                'column' => 'test_column',
                'boolean' => 'and',
                "values" => ['foo', 'bar'],
            ],
        ];
        $this->assertEquals($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\IsNotInClause
     */
    public function testIsNotInFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(IsNotInClause::class, ['foo', 'bar'], 'test_column');

        $where = [
            [
                'type' => 'NotIn',
                'column' => 'test_column',
                'boolean' => 'and',
                "values" => ['foo', 'bar'],
            ],
        ];
        $this->assertEquals($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\LessThanClause
     */
    public function testLessThanFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(LessThanClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "<",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\LessThanOrEqualsClause
     */
    public function testLessThanOrEqualsFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(LessThanOrEqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "<=",
                "value" => "foobar",
                "boolean" => "and",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    /**
     * @covers \Myerscode\Laravel\QueryStrategies\Clause\OrEqualsClause
     */
    public function testOrEqualsFilterClause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $filter->applyFilter(OrEqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                "type" => "Basic",
                "column" => 'test_column',
                "operator" => "=",
                "value" => "foobar",
                "boolean" => "or",
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }
}
