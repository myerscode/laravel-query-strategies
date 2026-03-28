<?php

declare(strict_types=1);

namespace Tests;

use Iterator;
use Illuminate\Database\Eloquent\Builder;
use Myerscode\Laravel\QueryStrategies\Clause\EqualsClause;
use Myerscode\Laravel\QueryStrategies\Filter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Models\Item;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;
use Tests\Support\Strategies\OverrideQueryStrategy;

#[CoversClass(Filter::class)]
final class FilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->simpleDatabase($this->app);
    }

    public static function providerForApplyOrder(): Iterator
    {
        yield 'no sort and order specified' => [
            'select * from "items"',
            ComplexConfigQueryStrategy::class,
            [],
        ];
        yield 'order by allowed value' => [
            'select * from "items" order by "id" asc',
            ComplexConfigQueryStrategy::class,
            ['order' => 'id'],
        ];
        yield 'sort by allowed value' => [
            'select * from "items" order by "id" desc',
            ComplexConfigQueryStrategy::class,
            ['order' => 'id', 'sort' => 'desc'],
        ];
        yield 'invalid sort resorts to default' => [
            'select * from "items" order by "id" asc',
            ComplexConfigQueryStrategy::class,
            ['order' => 'id', 'sort' => 'foobar'],
        ];
        yield 'order and sort by value values' => [
            'select * from "items" order by "name" desc',
            OverrideQueryStrategy::class,
            ['order' => 'name', 'sort' => 'desc'],
        ];
        yield 'order by multiple values' => [
            'select * from "items" order by "name" asc, "id" asc',
            OverrideQueryStrategy::class,
            ['order' => ['name', 'id'], 'sort' => 'asc'],
        ];
        yield 'set sort in order parameter' => [
            'select * from "items" order by "id" asc, "created" desc',
            OverrideQueryStrategy::class,
            ['order' => ['asc' => 'id', 'desc' => 'created',]],
        ];
        yield 'different sorts set in order with multiple order bys' => [
            'select * from "items" order by "id" asc, "name" asc, "created" desc',
            OverrideQueryStrategy::class,
            ['order' => ['asc' => ['id', 'name'], 'desc' => ['created']]],
        ];
        yield 'set sorts to order in sort parameter' => [
            'select * from "items" order by "id" desc, "name" asc',
            OverrideQueryStrategy::class,
            ['order' => ['id', 'name'], 'sort' => ['id' => 'desc', 'name' => 'asc',]],
        ];
        yield 'default sort for none named 1' => [
            'select * from "items" order by "id" asc, "name" desc',
            OverrideQueryStrategy::class,
            ['order' => ['id', 'name'], 'sort' => ['asc', 'name' => 'desc',]],
        ];
        yield 'default sort for none named 2' => [
            'select * from "items" order by "id" desc, "name" asc',
            OverrideQueryStrategy::class,
            ['order' => ['id', 'name'], 'sort' => ['name' => 'asc', 'desc',]],
        ];
        yield 'mix of where sorting it set' => [
            'select * from "items" order by "id" desc, "created" desc',
            OverrideQueryStrategy::class,
            ['order' => ['id', 'desc' => 'created'], 'sort' => ['name' => 'asc', 'desc',]],
        ];
    }

    public static function providerForApplyStrategy(): Iterator
    {
        yield 'no query parameters, only basics applied' => [
            'select * from "items" limit 50',
            ComplexConfigQueryStrategy::class,
            [],
        ];
        yield 'only a query parameter from the strategy is applied' => [
            'select * from "items" where "foo" = \'bar\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['foo' => 'bar', 'world' => 'foo'],
        ];
        yield 'multiple query parameters from the strategy is applied' => [
            'select * from "items" where "foo" = \'bar\' and "bar" = \'foo\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['foo' => 'bar', 'bar' => 'foo'],
        ];
        yield 'the multi filter is applied when default value for a parameter is an array' => [
            'select * from "items" where "foobar" in (\'foo\', \'bar\') limit 50',
            ComplexConfigQueryStrategy::class,
            ['foobar' => ['foo', 'bar']],
        ];
        yield 'use override parameter to set filter' => [
            'select * from "items" where "foo" like \'bar%\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['foo' => 'bar', 'foo--operator' => 'begins'],
        ];
        yield 'parameter with overridden default filter' => [
            'select * from "items" where "hello" != \'bar\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['hello' => 'bar'],
        ];
        yield 'should not apply disabled filter' => [
            'select * from "items" limit 50',
            ComplexConfigQueryStrategy::class,
            ['hello' => ['equals' => 'bar']],
        ];
        yield 'apply an allowed order' => [
            'select * from "items" order by "id" asc limit 50',
            ComplexConfigQueryStrategy::class,
            ['order' => 'id'],
        ];
        yield 'apply an allowed order and sort' => [
            'select * from "items" order by "id" desc limit 50',
            ComplexConfigQueryStrategy::class,
            ['order' => 'id', 'sort' => 'desc'],
        ];
        yield 'apply an allowed order and resort do default sort' => [
            'select * from "items" order by "id" asc limit 50',
            ComplexConfigQueryStrategy::class,
            ['order' => 'id', 'sort' => 'foobar'],
        ];
        yield 'a parameter alias is used' => [
            'select * from "items" where "bar_foo" = \'hello\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['bf' => 'hello'],
        ];
        yield 'multiple parameters are found by exploding' => [
            'select * from "items" where "explodable" in (\'foo\', \'bar\') limit 50',
            ComplexConfigQueryStrategy::class,
            ['explodable' => 'foo,bar'],
        ];
        yield 'multiple parameters are found by exploding array of values' => [
            'select * from "items" where "explodable" in (\'foo\', \'bar\', \'hello\', \'world\') limit 50',
            ComplexConfigQueryStrategy::class,
            ['explodable' => ['foo,bar', 'hello,world']],
        ];
        yield 'multiple parameters are found by exploding with custom delimiter' => [
            'select * from "items" where "exploding" in (\'foo\', \'bar\') limit 50',
            ComplexConfigQueryStrategy::class,
            ['exploding' => 'foo||bar'],
        ];
        yield 'parameter is not exploded if not enabled' => [
            'select * from "items" where "foo" = \'foo,bar\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['foo' => 'foo,bar'],
        ];
        yield 'named overrides can be exploded' => [
            'select * from "items" where "can_split" = \'hello\' or "can_split" = \'world\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['can_split' => ['or' => 'hello,world']],
        ];
    }

    public static function providerForFieldOperatorApply(): Iterator
    {
        yield 'single field--operator' => [
            'select * from "items" where "foo" like \'%bar%\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['foo--contains' => 'bar'],
        ];
        yield 'merge fields with same name' => [
            'select * from "items" where "foo" = \'bar\' and "foo" like \'%bar%\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['foo--contains' => 'bar', 'foo' => 'bar'],
        ];
    }

    public static function providerForGetQueryValues(): Iterator
    {
        yield 'example 1' => [
            ComplexConfigQueryStrategy::class,
            ['foo' => [1, 2, 3, 4], 'bar' => 'test'],
            ['foo' => [1, 2, 3, 4], 'bar' => ['test']],
        ];
        yield 'ignore none applicable values' => [
            ComplexConfigQueryStrategy::class,
            ['foo' => [1, 2, 3, 4], 'foo-bar' => 'should not appear'],
            ['foo' => [1, 2, 3, 4]],
        ];
        yield 'get split values' => [
            ComplexConfigQueryStrategy::class,
            ['explodable' => 'foo,bar'],
            ['explodable' => ['foo', 'bar']],
        ];
        yield 'get field alias values' => [
            ComplexConfigQueryStrategy::class,
            ['bf' => 'test'],
            ['bf' => ['test']],
        ];
    }

    public static function providerForPagination(): Iterator
    {
        yield 'default limit value' => [
            50,
            ComplexConfigQueryStrategy::class,
            [],
        ];
        yield 'user limits via query' => [
            5,
            ComplexConfigQueryStrategy::class,
            ['limit' => 5],
        ];
        yield 'no negative value' => [
            50,
            ComplexConfigQueryStrategy::class,
            ['limit' => '-1'],
        ];
        yield 'zero value falls back to paginator default' => [
            15,
            ComplexConfigQueryStrategy::class,
            ['limit' => '0'],
        ];
        yield 'low value' => [
            1,
            ComplexConfigQueryStrategy::class,
            ['limit' => '1'],
        ];
        yield 'user exceeds max limit' => [
            150,
            ComplexConfigQueryStrategy::class,
            ['limit' => 500_000_000_000],
        ];
    }

    public static function providerForWith(): Iterator
    {
        yield 'with single relation' => [
            ['owner'],
            ['with' => 'owner'],
        ];
        yield 'with multi via array' => [
            ['owner', 'categories'],
            ['with' => ['owner', 'categories']],
        ];
        yield 'with multi via comma separated' => [
            ['owner', 'categories'],
            ['with' => 'owner,categories'],
        ];
    }

    public function test_apply_filter(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy());
        $filter->applyFilter(EqualsClause::class, 'bar', 'foo');

        $builder = $filter->builder();
        $this->assertSame('select * from "items" where "foo" = \'bar\'', $this->getRawSqlFromBuilder($builder));
    }

    #[DataProvider('providerForApplyOrder')]
    public function test_apply_order(mixed $expectedSql, string $strategyClass, array $requestParams): void
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        $filter = $this->filter(Item::query(), $strategy, $requestParams);
        $builder = $filter->order()->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    #[DataProvider('providerForPagination')]
    public function test_apply_pagination(mixed $expectedLimit, string $strategyClass, array $requestParams): void
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        $filter = $this->filter(Item::query(), $strategy, $requestParams);
        $paginated = $filter->paginate();

        $this->assertEquals($expectedLimit, $paginated->perPage());
    }

    #[DataProvider('providerForApplyStrategy')]
    public function test_apply_the_strategy(mixed $expectedSql, string $strategyClass, array $requestParams): void
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        $filter = $this->filter(Item::query(), $strategy, $requestParams);
        $filter->apply();

        $builder = $filter->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    #[DataProvider('providerForWith')]
    public function test_apply_with(mixed $expectedEagerLoads, array $parameters): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), $parameters);
        $builder = $filter->with()->builder();
        $this->assertEquals($expectedEagerLoads, array_keys($builder->getEagerLoads()));
    }

    public function test_can_get_builder(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $filter = $this->filter(Item::query(), $strategy);
        $this->assertInstanceOf(Builder::class, $filter->builder());
    }

    #[DataProvider('providerForGetQueryValues')]
    public function test_can_get_get_query_values_that_will_be_applied(string $stategy, array $query, mixed $expect): void
    {
        $strategy = $this->strategyManager()->findStrategy($stategy);
        $filter = $this->filter(Item::query(), $strategy, $query);

        $this->assertEquals($expect, $filter->filterValues());
        $this->assertEquals($expect, $filter->paginate()->getAppliedFilters());
    }

    public function test_config_can_override_default_multi_clause(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $filter = $this->filter(Item::query(), $strategy, ['multi_override' => [1,2,3,4]]);
        $filter->apply();

        $builder = $filter->builder();
        $expectedSql = 'select * from "items" where "multi_override" = \'1+2+3+4\' limit 50';
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    public function test_config_changes_limit_key(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['limit' => '7', 'l' => '49'], ['limit' => 'l']);
        $filter->limit();

        $builder = $filter->builder();
        $this->assertSame('select * from "items" limit 49', $this->getRawSqlFromBuilder($builder));
    }

    public function test_config_changes_order_key(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['order' => 'id', 'o' => 'name'], ['order' => 'o']);
        $filter->order();

        $builder = $filter->builder();
        $this->assertSame('select * from "items" order by "name" asc', $this->getRawSqlFromBuilder($builder));
    }

    public function test_config_changes_sort_key(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['order' => 'name', 'sort' => 'asc', 's' => 'desc'], ['sort' => 's']);
        $filter->order();

        $builder = $filter->builder();
        $this->assertSame('select * from "items" order by "name" desc', $this->getRawSqlFromBuilder($builder));
    }

    public function test_config_changes_with_key(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['with' => ['owner', 'categories'] , 'w' => 'owner'], ['with' => 'w']);
        $builder = $filter->with()->builder();
        $this->assertSame(['owner'], array_keys($builder->getEagerLoads()));
    }

    public function test_creates_instance_of_distill(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $filter = new Filter(Item::query(), $strategy, []);
        $this->assertInstanceOf(Filter::class, $filter);
    }

    #[DataProvider('providerForFieldOperatorApply')]
    public function test_field_operator_properties_found_and_applied(mixed $expectedSql, string $strategyClass, array $requestParams): void
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        $filter = $this->filter(Item::query(), $strategy, $requestParams);
        $filter->apply();

        $builder = $filter->builder();
        $this->assertEquals($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    public function test_filter_ignores_unknown_parameters(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $filter = $this->filter(Item::query(), $strategy, ['unknown_param' => 'value']);
        $filter->filter();

        $this->assertSame('select * from "items"', $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_filter_values_ignores_unknown_parameters(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $filter = $this->filter(Item::query(), $strategy, ['unknown_param' => 'value']);

        $this->assertEmpty($filter->filterValues());
    }

    public function test_get_parameter_methods_returns_defaults_for_unknown_parameter(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $filter = $this->filter(Item::query(), $strategy);

        $methods = $filter->getParameterMethods('nonexistent');
        $this->assertEquals($strategy->defaultMethods(), $methods);
    }

    public function test_multi_override_should_not_have_priorities_on_overrides(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $filter = $this->filter(Item::query(), $strategy, ['override_this' => ['lookup' => [1,2,3,4]]]);
        $filter->apply();

        $builder = $filter->builder();
        $expectedSql = 'select * from "items" where "override_this" = \'1&2&3&4\' limit 50';
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    public function test_triple_dash_parameter_is_ignored(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $filter = $this->filter(Item::query(), $strategy, ['foo--bar--baz' => 'value']);
        $filter->filter();

        $this->assertSame('select * from "items"', $this->getRawSqlFromBuilder($filter->builder()));
    }
}
