<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Myerscode\Laravel\QueryStrategies\Clause\EqualsClause;
use Myerscode\Laravel\QueryStrategies\Filter;
use Tests\Support\Models\Item;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;
use Tests\Support\Strategies\OverrideQueryStrategy;

/**
 * @coversDefaultClass \Myerscode\Laravel\QueryStrategies\Filter
 */
class FilterTest extends TestCase
{

    public function providerForApplyStrategy()
    {
        return [
            'no query parameters, only basics applied' => [
                'select * from "items" limit 50',
                ComplexConfigQueryStrategy::class,
                []
            ],
            'only a query parameter from the strategy is applied' => [
                'select * from "items" where "foo" = \'bar\' limit 50',
                ComplexConfigQueryStrategy::class,
                ['foo' => 'bar', 'world' => 'foo']
            ],
            'multiple query parameters from the strategy is applied' => [
                'select * from "items" where "foo" = \'bar\' and "bar" = \'foo\' limit 50',
                ComplexConfigQueryStrategy::class,
                ['foo' => 'bar', 'bar' => 'foo']
            ],
            'the multi filter is applied when default value for a parameter is an array' => [
                'select * from "items" where "foobar" in (\'foo\', \'bar\') limit 50',
                ComplexConfigQueryStrategy::class,
                ['foobar' => ['foo', 'bar']]
            ],
            'use override parameter to set filter' => [
                'select * from "items" where "foo" like \'bar%\' limit 50',
                ComplexConfigQueryStrategy::class,
                ['foo' => 'bar', 'foo--operator' => 'begins']
            ],
            'parameter with overridden default filter' => [
                'select * from "items" where "hello" != \'bar\' limit 50',
                ComplexConfigQueryStrategy::class,
                ['hello' => 'bar']
            ],
            'should not apply disabled filter' => [
                'select * from "items" limit 50',
                ComplexConfigQueryStrategy::class,
                ['hello' => ['equals' => 'bar']]
            ],
            'apply an allowed order' => [
                'select * from "items" order by "id" asc limit 50',
                ComplexConfigQueryStrategy::class,
                ['order' => 'id']
            ],
            'apply an allowed order and sort' => [
                'select * from "items" order by "id" desc limit 50',
                ComplexConfigQueryStrategy::class,
                ['order' => 'id', 'sort' => 'desc']
            ],
            'apply an allowed order and resort do default sort' => [
                'select * from "items" order by "id" asc limit 50',
                ComplexConfigQueryStrategy::class,
                ['order' => 'id', 'sort' => 'foobar']
            ],
            'a parameter alias is used' => [
                'select * from "items" where "bar_foo" = \'hello\' limit 50',
                ComplexConfigQueryStrategy::class,
                ['bf' => 'hello']
            ],
            'multiple parameters are found by exploding' => [
                'select * from "items" where "explodable" in (\'foo\', \'bar\') limit 50',
                ComplexConfigQueryStrategy::class,
                ['explodable' => 'foo,bar']
            ],
            'multiple parameters are found by exploding array of values' => [
                'select * from "items" where "explodable" in (\'foo\', \'bar\', \'hello\', \'world\') limit 50',
                ComplexConfigQueryStrategy::class,
                ['explodable' => ['foo,bar', 'hello,world']]
            ],
            'multiple parameters are found by exploding with custom delimiter' => [
                'select * from "items" where "exploding" in (\'foo\', \'bar\') limit 50',
                ComplexConfigQueryStrategy::class,
                ['exploding' => 'foo||bar']
            ],
            'parameter is not exploded if not enabled' => [
                'select * from "items" where "foo" = \'foo,bar\' limit 50',
                ComplexConfigQueryStrategy::class,
                ['foo' => 'foo,bar']
            ],
            'named overrides can be exploded' => [
                'select * from "items" where "can_split" = \'hello\' or "can_split" = \'world\' limit 50',
                ComplexConfigQueryStrategy::class,
                ['can_split' => ['or' => 'hello,world']]
            ],
        ];
    }

    public function providerForApplyOrder()
    {
        return [
            'no sort and order specified' => [
                'select * from "items"',
                ComplexConfigQueryStrategy::class,
                []
            ],
            'order by allowed value' => [
                'select * from "items" order by "id" asc',
                ComplexConfigQueryStrategy::class,
                ['order' => 'id']
            ],
            'sort by allowed value' => [
                'select * from "items" order by "id" desc',
                ComplexConfigQueryStrategy::class,
                ['order' => 'id', 'sort' => 'desc']
            ],
            'invalid sort resorts to default' => [
                'select * from "items" order by "id" asc',
                ComplexConfigQueryStrategy::class,
                ['order' => 'id', 'sort' => 'foobar']
            ],
            'order and sort by value values' => [
                'select * from "items" order by "name" desc',
                OverrideQueryStrategy::class,
                ['order' => 'name', 'sort' => 'desc']
            ],
            'order by multiple values' => [
                'select * from "items" order by "name" asc, "id" asc',
                OverrideQueryStrategy::class,
                ['order' => ['name', 'id'], 'sort' => 'asc']
            ],
            'set sort in order parameter' => [
                'select * from "items" order by "id" asc, "created" desc',
                OverrideQueryStrategy::class,
                ['order' => ['asc' => 'id', 'desc' => 'created',]]
            ],
            'different sorts set in order with multiple order bys' => [
                'select * from "items" order by "id" asc, "name" asc, "created" desc',
                OverrideQueryStrategy::class,
                ['order' => ['asc' => ['id', 'name'], 'desc' => ['created']]]
            ],
            'set sorts to order in sort parameter' => [
                'select * from "items" order by "id" desc, "name" asc',
                OverrideQueryStrategy::class,
                ['order' => ['id', 'name'], 'sort' => ['id' => 'desc', 'name' => 'asc',]]
            ],
            'default sort for none named 1' => [
                'select * from "items" order by "id" asc, "name" desc',
                OverrideQueryStrategy::class,
                ['order' => ['id', 'name'], 'sort' => ['asc', 'name' => 'desc',]]
            ],
            'default sort for none named 2' => [
                'select * from "items" order by "id" desc, "name" asc',
                OverrideQueryStrategy::class,
                ['order' => ['id', 'name'], 'sort' => ['name' => 'asc', 'desc',]]
            ],
            'mix of where sorting it set' => [
                'select * from "items" order by "id" desc, "created" desc',
                OverrideQueryStrategy::class,
                ['order' => ['id', 'desc' => 'created'], 'sort' => ['name' => 'asc', 'desc',]]
            ],
        ];
    }

    public function providerForPagination()
    {
        return [
            'default limit value' => [
                'select * from "items" limit 50',
                ComplexConfigQueryStrategy::class,
                []
            ],
            'user limits via query' => [
                'select * from "items" limit 5',
                ComplexConfigQueryStrategy::class,
                ['limit' => 5]
            ],
            'no negative value' => [
                'select * from "items" limit 50',
                ComplexConfigQueryStrategy::class,
                ['limit' => '-1']
            ],
            'zero value' => [
                'select * from "items" limit 0',
                ComplexConfigQueryStrategy::class,
                ['limit' => '0']
            ],
            'low value' => [
                'select * from "items" limit 1',
                ComplexConfigQueryStrategy::class,
                ['limit' => '1']
            ],
            'user exceeds max limit' => [
                'select * from "items" limit 150',
                ComplexConfigQueryStrategy::class,
                ['limit' => 500000000000]
            ],
        ];
    }

    public function providerForWith()
    {
        return [
            'with single relation' => [
                ['owner'],
                ['with' => 'owner']
            ],
            'with multi via array' => [
                ['owner', 'categories'],
                ['with' => ['owner', 'categories']]
            ],
            'with multi via comma separated' => [
                ['owner', 'categories'],
                ['with' => 'owner,categories']
            ],
        ];
    }

    public function providerForFieldOperatorApply()
    {
        return [
            'single field--operator' => [
                'select * from "items" where "foo" like \'%bar%\' limit 50',
                ComplexConfigQueryStrategy::class,
                ['foo--contains' => 'bar']
            ],
            'merge fields with same name' => [
                'select * from "items" where "foo" = \'bar\' and "foo" like \'%bar%\' limit 50',
                ComplexConfigQueryStrategy::class,
                ['foo--contains' => 'bar', 'foo' => 'bar']
            ],
        ];
    }

    public function providerForGetQueryValues()
    {
        return [
            'example 1' => [
                ComplexConfigQueryStrategy::class,
                ['foo' =>  [1,2,3,4], 'bar' => 'test'],
                ['foo' =>  [1,2,3,4], 'bar' => ['test']],
            ],
            'ignore none applicable values' => [
                ComplexConfigQueryStrategy::class,
                ['foo' =>  [1,2,3,4], 'foo-bar' =>  'should not appear'],
                ['foo' =>  [1,2,3,4]],
            ],
            'get split values' => [
                ComplexConfigQueryStrategy::class,
                ['explodable' => 'foo,bar'],
                ['explodable' => ['foo', 'bar']],
            ],
            'get field alias values' => [
                ComplexConfigQueryStrategy::class,
                ['bf' => 'test'],
                ['bf' => ['test']],
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleDatabase($this->app);
    }

    public function testCreatesInstanceOfDistill()
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $distill = new Filter(Item::query(), $strategy, []);
        $this->assertInstanceOf(Filter::class, $distill);
    }

    public function testCanGetBuilder()
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $distill = $this->filter(Item::query(), $strategy);
        $this->assertInstanceOf(Builder::class, $distill->builder());
    }

    /**
     * @dataProvider providerForApplyStrategy
     */
    public function testApplyTheStrategy($expectedSql, $strategyClass, $requestParams)
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        $distill = $this->filter(Item::query(), $strategy, $requestParams);
        $distill->apply();
        $builder = $distill->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    /**
     * @dataProvider providerForFieldOperatorApply
     */
    public function testFieldOperatorPropertiesFoundAndApplied($expectedSql, $strategyClass, $requestParams)
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        $distill = $this->filter(Item::query(), $strategy, $requestParams);
        $distill->apply();
        $builder = $distill->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    /**
     * @dataProvider providerForApplyOrder
     */
    public function testApplyOrder($expectedSql, $strategyClass, $requestParams)
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        $distill = $this->filter(Item::query(), $strategy, $requestParams);
        $builder = $distill->order()->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    /**
     * @dataProvider providerForPagination
     */
    public function testApplyPagination($expectedSql, $strategyClass, $requestParams)
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        $distill = $this->filter(Item::query(), $strategy, $requestParams);
        $distill->paginate();
        $builder = $distill->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    /**
     * @dataProvider providerForWith
     */
    public function testApplyWith($expectedEagerLoads, $parameters)
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy, $parameters);
        $builder = $distill->with()->builder();
        $this->assertEquals($expectedEagerLoads, array_keys($builder->getEagerLoads()));
    }

    public function testApplyFilter()
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy);
        $distill->applyFilter(EqualsClause::class, 'bar', 'foo');
        $builder = $distill->builder();
        $this->assertEquals('select * from "items" where "foo" = \'bar\'', $this->getRawSqlFromBuilder($builder));
    }

    public function testConfigChangesLimitKey()
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy, ['limit' => '7', 'l' => '49'], ['limit' => 'l']);
        $distill->limit();
        $builder = $distill->builder();
        $this->assertEquals('select * from "items" limit 49', $this->getRawSqlFromBuilder($builder));
    }

    public function testConfigChangesOrderKey()
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy, ['order' => 'id', 'o' => 'name'], ['order' => 'o']);
        $distill->order();
        $builder = $distill->builder();
        $this->assertEquals('select * from "items" order by "name" asc', $this->getRawSqlFromBuilder($builder));
    }

    public function testConfigChangesSortKey()
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy, ['order' => 'name', 'sort' => 'asc', 's' => 'desc'], ['sort' => 's']);
        $distill->order();
        $builder = $distill->builder();
        $this->assertEquals('select * from "items" order by "name" desc', $this->getRawSqlFromBuilder($builder));
    }

    public function testConfigChangesWithKey()
    {
        $distill = $this->filter(Item::query(), new ComplexConfigQueryStrategy, ['with' => ['owner', 'categories'] , 'w' => 'owner'], ['with' => 'w']);
        $builder = $distill->with()->builder();
        $this->assertEquals(['owner'], array_keys($builder->getEagerLoads()));
    }

    public function testConfigCanOverrideDefaultMultiClause()
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $distill = $this->filter(Item::query(), $strategy, ['multi_override' => [1,2,3,4]]);
        $distill->apply();
        $builder = $distill->builder();
        $expectedSql = 'select * from "items" where "multi_override" = \'1+2+3+4\' limit 50';
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    public function testMultiOverrideShouldNotHavePrioritiesOnOverrides()
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $distill = $this->filter(Item::query(), $strategy, ['override_this' => ['lookup' => [1,2,3,4]]]);
        $distill->apply();
        $builder = $distill->builder();
        $expectedSql = 'select * from "items" where "override_this" = \'1&2&3&4\' limit 50';
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    /**
     * @dataProvider providerForGetQueryValues
     */
    public function testCanGetGetQueryValuesThatWillBeApplied($stategy, $query, $expect)
    {
        $strategy = $this->strategyManager()->findStrategy($stategy);
        $distill = $this->filter(Item::query(), $strategy, $query);

        $this->assertEquals($expect, $distill->filterValues());
    }
}
