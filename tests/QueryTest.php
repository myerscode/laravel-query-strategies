<?php

namespace Tests;

use Iterator;
use Tests\Support\Models\Item;
use Tests\Support\Strategies\BasicConfigQueryStrategy;

class QueryTest extends TestCase
{

    public static function provider(): Iterator
    {
        yield 'no values' => [
            '',
            'select * from "items"',
        ];
        yield 'default query' => [
            'foo=bar',
            'select * from "items" where "foo" = \'bar\'',
        ];
        yield 'beingsWith query' => [
            'foo[beginsWith]=bar',
            'select * from "items" where "foo" like \'bar%\'',
        ];
        yield '*% query' => [
            'foo[*%]=bar',
            'select * from "items" where "foo" like \'bar%\'',
        ];
        yield 'contains query' => [
            'foo[contains]=bar',
            'select * from "items" where "foo" like \'%bar%\'',
        ];
        yield 'endsWith query' => [
            'foo[endsWith]=bar',
            'select * from "items" where "foo" like \'%bar\'',
        ];
        yield 'lessThan query' => [
            'foo[lessThan]=bar',
            'select * from "items" where "foo" < \'bar\'',
        ];
        yield 'lessThanOrEquals query' => [
            'foo[lessThanOrEquals]=bar',
            'select * from "items" where "foo" <= \'bar\'',
        ];
        yield 'greaterThan query' => [
            'foo[greaterThan]=bar',
            'select * from "items" where "foo" > \'bar\'',
        ];
        yield 'greaterThanOrEquals query' => [
            'foo[greaterThanOrEquals]=bar',
            'select * from "items" where "foo" >= \'bar\'',
        ];
        yield 'is query' => [
            'foo[is]=bar',
            'select * from "items" where "foo" = \'bar\'',
        ];
        yield 'not query' => [
            'foo[not]=bar',
            'select * from "items" where "foo" != \'bar\'',
        ];
        yield 'isIn query' => [
            'foo[isIn]=bar',
            'select * from "items" where "foo" in (\'bar\')',
        ];
        yield 'isIn comma separated query' => [
            'foo[isIn]=foo,bar,hello,world',
            'select * from "items" where "foo" in (\'foo\', \'bar\', \'hello\', \'world\')',
        ];
        yield 'isIn default query' => [
            'foo[]=foo&foo[]=bar&foo[]=hello&foo[]=world',
            'select * from "items" where "foo" in (\'foo\', \'bar\', \'hello\', \'world\')',
        ];
        yield 'notIn query' => [
            'foo[notIn]=bar',
            'select * from "items" where "foo" not in (\'bar\')',
        ];
        yield 'notIn comma separated query' => [
            'foo[notIn]=foo,bar,hello,world',
            'select * from "items" where "foo" not in (\'foo\', \'bar\', \'hello\', \'world\')',
        ];
        yield 'or query' => [
            'foo[is]=bar&foo[or]=bar',
            'select * from "items" where "foo" = \'bar\' or "foo" = \'bar\'',
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testApplyTheStrategy(string $queryString, mixed $expectedSql): void
    {
        $requestParams = [];
        parse_str($queryString, $requestParams);
        $strategy = $this->strategyManager()->findStrategy(BasicConfigQueryStrategy::class);
        $builder = $this->filter(Item::query(), $strategy, $requestParams)->filter()->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    public function testParameterOperator(): void
    {
        $requestParams = [];
        parse_str('foo--contains=bar', $requestParams);
        $expectedSql = 'select * from "items" where "foo" like \'%bar%\'';
        $strategy = $this->strategyManager()->findStrategy(BasicConfigQueryStrategy::class);
        $builder = $this->filter(Item::query(), $strategy, $requestParams)->filter()->builder();
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($builder));
    }
}
