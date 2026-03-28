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
    public function test_begins_with_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(BeginsWithClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => 'like',
                'value' => 'foobar%',
                'boolean' => 'and',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_contains_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(ContainsClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => 'like',
                'value' => '%foobar%',
                'boolean' => 'and',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_does_not_equal_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(DoesNotEqualClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => '!=',
                'value' => 'foobar',
                'boolean' => 'and',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_ends_with_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(EndsWithClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => 'like',
                'value' => '%foobar',
                'boolean' => 'and',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_equals_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(EqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => '=',
                'value' => 'foobar',
                'boolean' => 'and',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_greater_than_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(GreaterThanClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => '>',
                'value' => 'foobar',
                'boolean' => 'and',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_greater_than_or_equals_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(GreaterThanOrEqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => '>=',
                'value' => 'foobar',
                'boolean' => 'and',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_is_in_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(IsInClause::class, ['foo', 'bar'], 'test_column');

        $where = [
            [
                'type' => 'In',
                'column' => 'test_column',
                'boolean' => 'and',
                'values' => ['foo', 'bar'],
            ],
        ];
        $this->assertEquals($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_is_not_in_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(IsNotInClause::class, ['foo', 'bar'], 'test_column');

        $where = [
            [
                'type' => 'NotIn',
                'column' => 'test_column',
                'boolean' => 'and',
                'values' => ['foo', 'bar'],
            ],
        ];
        $this->assertEquals($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_less_than_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(LessThanClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => '<',
                'value' => 'foobar',
                'boolean' => 'and',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_less_than_or_equals_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(LessThanOrEqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => '<=',
                'value' => 'foobar',
                'boolean' => 'and',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }

    public function test_or_equals_filter_clause(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(OrEqualsClause::class, 'foobar', 'test_column');

        $where = [
            [
                'type' => 'Basic',
                'column' => 'test_column',
                'operator' => '=',
                'value' => 'foobar',
                'boolean' => 'or',
            ],
        ];
        $this->assertSame($where, $filter->builder()->getQuery()->wheres);
    }
}
