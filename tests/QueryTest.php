<?php

namespace Tests;

use Tests\Support\Models\Item;
use Tests\Support\Strategies\BasicConfigQueryStrategy;

class QueryTest extends TestCase
{

    public function provider()
    {
        return [
            'no values' => [
                '',
                'select * from "items"',
            ],
            'default query' => [
                'foo=bar',
                'select * from "items" where "foo" = \'bar\'',
            ],
            'beingsWith query' => [
                'foo[beginsWith]=bar',
                'select * from "items" where "foo" like \'bar%\'',
            ],
            '*% query' => [
                'foo[*%]=bar',
                'select * from "items" where "foo" like \'bar%\'',
            ],
            'contains query' => [
                'foo[contains]=bar',
                'select * from "items" where "foo" like \'%bar%\'',
            ],
            'endsWith query' => [
                'foo[endsWith]=bar',
                'select * from "items" where "foo" like \'%bar\'',
            ],
            'lessThan query' => [
                'foo[lessThan]=bar',
                'select * from "items" where "foo" < \'bar\'',
            ],
            'lessThanOrEquals query' => [
                'foo[lessThanOrEquals]=bar',
                'select * from "items" where "foo" <= \'bar\'',
            ],
            'greaterThan query' => [
                'foo[greaterThan]=bar',
                'select * from "items" where "foo" > \'bar\'',
            ],
            'greaterThanOrEquals query' => [
                'foo[greaterThanOrEquals]=bar',
                'select * from "items" where "foo" >= \'bar\'',
            ],
            'is query' => [
                'foo[is]=bar',
                'select * from "items" where "foo" = \'bar\'',
            ],
            'not query' => [
                'foo[not]=bar',
                'select * from "items" where "foo" != \'bar\'',
            ],
            'isIn query' => [
                'foo[isIn]=bar',
                'select * from "items" where "foo" in (\'bar\')',
            ],
            'isIn comma separated query' => [
                'foo[isIn]=foo,bar,hello,world',
                'select * from "items" where "foo" in (\'foo\', \'bar\', \'hello\', \'world\')',
            ],
            'isIn default query' => [
                'foo[]=foo&foo[]=bar&foo[]=hello&foo[]=world',
                'select * from "items" where "foo" in (\'foo\', \'bar\', \'hello\', \'world\')',
            ],
            'notIn query' => [
                'foo[notIn]=bar',
                'select * from "items" where "foo" not in (\'bar\')',
            ],
            'notIn comma separated query' => [
                'foo[notIn]=foo,bar,hello,world',
                'select * from "items" where "foo" not in (\'foo\', \'bar\', \'hello\', \'world\')',
            ],
            'or query' => [
                'foo[is]=bar&foo[or]=bar',
                'select * from "items" where "foo" = \'bar\' or "foo" = \'bar\'',
            ],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testApplyTheStrategy($queryString, $expectedSql)
    {
        $requestParams = [];
        parse_str($queryString, $requestParams);
        $strategy = $this->strategyManager()->findStrategy(BasicConfigQueryStrategy::class);
        $builder = $this->filter(Item::query(), $strategy, $requestParams)->filter()->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    public function testParameterOperator()
    {
        $requestParams = [];
        parse_str('foo--contains=bar', $requestParams);
        $expectedSql = 'select * from "items" where "foo" like \'%bar%\'';
        $strategy = $this->strategyManager()->findStrategy(BasicConfigQueryStrategy::class);
        $builder = $this->filter(Item::query(), $strategy, $requestParams)->filter()->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }
}
