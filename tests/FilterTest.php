<?php

declare(strict_types=1);

namespace Tests;

use Iterator;
use Illuminate\Database\Eloquent\Builder;
use Myerscode\Laravel\QueryStrategies\Clause\EqualsClause;
use Myerscode\Laravel\QueryStrategies\Exceptions\InvalidFilterException;
use Myerscode\Laravel\QueryStrategies\Filter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Models\Item;
use Tests\Support\Models\SoftDeletableItem;
use Tests\Support\Strategies\AggregateIncludeStrategy;
use Tests\Support\Strategies\AppendStrategy;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;
use Tests\Support\Strategies\DefaultValueStrategy;
use Tests\Support\Strategies\FieldSelectionStrategy;
use Tests\Support\Strategies\IgnoredValuesStrategy;
use Tests\Support\Strategies\OverrideQueryStrategy;
use Tests\Support\Strategies\RelationshipFilterStrategy;
use Tests\Support\Strategies\RestrictedWithStrategy;
use Tests\Support\Strategies\ScopeFilterStrategy;
use Tests\Support\Strategies\TrashedFilterStrategy;

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

    public static function providerForDefaultValues(): Iterator
    {
        yield 'default values applied when not in request' => [
            'select * from "items" where "active" = \'1\' and "status" = \'published\'',
            [],
        ];
        yield 'request value overrides default' => [
            'select * from "items" where "active" = \'0\' and "status" = \'published\'',
            ['active' => '0'],
        ];
        yield 'all overridden' => [
            'select * from "items" where "active" = \'0\' and "status" = \'draft\'',
            ['active' => '0', 'status' => 'draft'],
        ];
        yield 'default with explicit request param' => [
            'select * from "items" where "name" = \'Foo\' and "active" = \'1\' and "status" = \'published\'',
            ['name' => 'Foo'],
        ];
        yield 'null default value is not applied' => [
            'select * from "items" where "active" = \'1\' and "status" = \'published\'',
            [],
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
        yield 'isNull operator override' => [
            'select * from "items" where "foo" is null limit 50',
            ComplexConfigQueryStrategy::class,
            ['foo--isNull' => ''],
        ];
        yield 'isNotNull operator override' => [
            'select * from "items" where "foo" is not null limit 50',
            ComplexConfigQueryStrategy::class,
            ['foo--isNotNull' => ''],
        ];
        yield 'between operator override' => [
            'select * from "items" where "foo" between \'10\' and \'100\' limit 50',
            ComplexConfigQueryStrategy::class,
            ['foo--between' => '10,100'],
        ];
    }

    public static function providerForFields(): Iterator
    {
        yield 'no fields requested selects all' => [
            'select * from "items"',
            FieldSelectionStrategy::class,
            [],
        ];
        yield 'select allowed fields' => [
            'select "id", "name" from "items"',
            FieldSelectionStrategy::class,
            ['fields' => 'id,name'],
        ];
        yield 'disallowed fields are filtered out' => [
            'select "id" from "items"',
            FieldSelectionStrategy::class,
            ['fields' => 'id,secret'],
        ];
        yield 'all disallowed fields keeps select star' => [
            'select * from "items"',
            FieldSelectionStrategy::class,
            ['fields' => 'secret,password'],
        ];
        yield 'fields via array' => [
            'select "id", "email" from "items"',
            FieldSelectionStrategy::class,
            ['fields' => ['id', 'email']],
        ];
        yield 'empty allowedFields allows all requested' => [
            'select "anything", "goes" from "items"',
            ComplexConfigQueryStrategy::class,
            ['fields' => 'anything,goes'],
        ];
        yield 'single field' => [
            'select "name" from "items"',
            FieldSelectionStrategy::class,
            ['fields' => 'name'],
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

    public static function providerForIgnoredValues(): Iterator
    {
        yield 'ignored value is not applied' => [
            'select * from "items"',
            ['name' => '-1'],
        ];
        yield 'non-ignored value is applied' => [
            'select * from "items" where "name" = \'John\'',
            ['name' => 'John'],
        ];
        yield 'ignored value filtered from array' => [
            'select * from "items" where "name" = \'John\'',
            ['name' => ['John', '-1']],
        ];
        yield 'all values ignored means no filter' => [
            'select * from "items"',
            ['name' => ['-1', 'all']],
        ];
        yield 'null ignored value' => [
            'select * from "items"',
            ['status' => null],
        ];
        yield 'non-ignored parameter unaffected' => [
            'select * from "items" where "type" = \'foo\'',
            ['type' => 'foo'],
        ];
        yield 'mixed ignored and valid across params' => [
            'select * from "items" where "type" = \'foo\'',
            ['name' => '-1', 'type' => 'foo'],
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

    public static function providerForRelationshipFilter(): Iterator
    {
        yield 'dot notation applies whereHas' => [
            'select * from "items" where exists (select * from "owners" where "items"."id" = "owners"."item_id" and "name" = \'John\')',
            ['owner.name' => 'John'],
        ];
        yield 'column alias with dot notation applies whereHas' => [
            'select * from "items" where exists (select * from "owners" where "items"."id" = "owners"."item_id" and "email" like \'%test%\')',
            ['owner_email' => 'test'],
        ];
        yield 'regular parameter still works alongside relationship' => [
            'select * from "items" where "name" = \'Foo\' and exists (select * from "owners" where "items"."id" = "owners"."item_id" and "name" = \'Bar\')',
            ['name' => 'Foo', 'owner.name' => 'Bar'],
        ];
    }

    public static function providerForScopeFilter(): Iterator
    {
        yield 'scope with single value' => [
            'select * from "items" where "starts_at" <= \'2024-01-01\'',
            ['starts_before' => '2024-01-01'],
        ];
        yield 'scope with comma-separated values' => [
            'select * from "items" where "created_at" between \'2024-01-01\' and \'2024-12-31\'',
            ['created_between' => '2024-01-01,2024-12-31'],
        ];
        yield 'scope with boolean-like value' => [
            'select * from "items" where "active" = \'1\'',
            ['active' => '1'],
        ];
        yield 'empty scope value is ignored' => [
            'select * from "items"',
            ['active' => ''],
        ];
        yield 'scope alongside regular filter' => [
            'select * from "items" where "name" = \'Foo\' and "starts_at" <= \'2024-06-01\'',
            ['name' => 'Foo', 'starts_before' => '2024-06-01'],
        ];
    }

    public static function providerForTrashedFilter(): Iterator
    {
        yield 'no trashed param keeps soft delete scope' => [
            'select * from "soft_items" where "soft_items"."deleted_at" is null',
            [],
        ];
        yield 'trashed=with includes soft deleted' => [
            'select * from "soft_items"',
            ['trashed' => 'with'],
        ];
        yield 'trashed=only returns only trashed' => [
            'select * from "soft_items" where "soft_items"."deleted_at" is not null',
            ['trashed' => 'only'],
        ];
        yield 'invalid trashed value keeps default scope' => [
            'select * from "soft_items" where "soft_items"."deleted_at" is null',
            ['trashed' => 'invalid'],
        ];
        yield 'trashed alongside regular filter' => [
            'select * from "soft_items" where "name" = \'Foo\' and "soft_items"."deleted_at" is null',
            ['name' => 'Foo', 'trashed' => 'nope'],
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

    public function test_aggregate_include_count(): void
    {
        $filter = $this->filter(Item::query(), new AggregateIncludeStrategy(), ['with' => 'ownerCount']);
        $builder = $filter->with()->builder();
        $this->assertStringContainsString('owner_count', $this->getRawSqlFromBuilder($builder));
    }

    public function test_aggregate_include_exists(): void
    {
        $filter = $this->filter(Item::query(), new AggregateIncludeStrategy(), ['with' => 'ownerExists']);
        $builder = $filter->with()->builder();
        $this->assertStringContainsString('owner_exists', $this->getRawSqlFromBuilder($builder));
    }

    public function test_aggregate_mixed_with_eager_load(): void
    {
        $filter = $this->filter(Item::query(), new AggregateIncludeStrategy(), ['with' => 'owner,ownerCount']);
        $builder = $filter->with()->builder();
        $this->assertSame(['owner'], array_keys($builder->getEagerLoads()));
        $this->assertStringContainsString('owner_count', $this->getRawSqlFromBuilder($builder));
    }

    public function test_aggregate_unknown_include_filtered_by_canwith(): void
    {
        $filter = $this->filter(Item::query(), new AggregateIncludeStrategy(), ['with' => 'secret']);
        $builder = $filter->with()->builder();
        $this->assertSame([], array_keys($builder->getEagerLoads()));
    }

    public function test_append_adds_accessors_to_results(): void
    {
        Item::create(['name' => 'Test', 'likes' => '0']);

        $filter = $this->filter(Item::query(), new AppendStrategy(), ['append' => 'display_name']);
        $paginated = $filter->apply();

        $first = $paginated->items()[0];
        $this->assertArrayHasKey('display_name', $first->toArray());
    }

    public function test_append_all_disallowed_appends_nothing(): void
    {
        Item::create(['name' => 'Test', 'likes' => '0']);

        $filter = $this->filter(Item::query(), new AppendStrategy(), ['append' => 'secret_value']);
        $paginated = $filter->apply();

        $first = $paginated->items()[0];
        $this->assertArrayNotHasKey('secret_value', $first->toArray());
        $this->assertArrayNotHasKey('display_name', $first->toArray());
    }

    public function test_append_filters_disallowed_accessors(): void
    {
        Item::create(['name' => 'Test', 'likes' => '0']);

        $filter = $this->filter(Item::query(), new AppendStrategy(), ['append' => 'display_name,secret_value']);
        $paginated = $filter->apply();

        $first = $paginated->items()[0];
        $this->assertArrayHasKey('display_name', $first->toArray());
        $this->assertArrayNotHasKey('secret_value', $first->toArray());
    }

    public function test_append_with_empty_allowed_permits_all(): void
    {
        Item::create(['name' => 'Test', 'likes' => '0']);

        $strategy = new class () extends \Myerscode\Laravel\QueryStrategies\Strategies\Strategy {
            protected array $config = ['name'];
        };

        $filter = $this->filter(Item::query(), $strategy, ['append' => 'display_name']);
        $paginated = $filter->apply();

        $first = $paginated->items()[0];
        $this->assertArrayHasKey('display_name', $first->toArray());
    }

    public function test_append_with_no_request_does_not_append(): void
    {
        Item::create(['name' => 'Test', 'likes' => '0']);

        $filter = $this->filter(Item::query(), new AppendStrategy(), []);
        $paginated = $filter->apply();

        $first = $paginated->items()[0];
        $this->assertArrayNotHasKey('display_name', $first->toArray());
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

    public function test_callback_filter(): void
    {
        $strategy = new class () extends \Myerscode\Laravel\QueryStrategies\Strategies\Strategy {
            protected array $config = [];

            public function __construct()
            {
                $this->config = [
                    'has_posts' => [
                        'callback' => static function ($builder, $value, $column): void {
                            $builder->where('post_count', '>', 0);
                        },
                    ],
                ];
                parent::__construct();
            }
        };

        $filter = new Filter(Item::query(), $strategy, ['has_posts' => '1']);
        $filter->filter();
        $this->assertSame('select * from "items" where "post_count" > \'0\'', $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_callback_filter_not_applied_when_absent(): void
    {
        $strategy = new class () extends \Myerscode\Laravel\QueryStrategies\Strategies\Strategy {
            protected array $config = [];

            public function __construct()
            {
                $this->config = [
                    'has_posts' => [
                        'callback' => static function ($builder, $value, $column): void {
                            $builder->where('post_count', '>', 0);
                        },
                    ],
                ];
                parent::__construct();
            }
        };

        $filter = new Filter(Item::query(), $strategy, []);
        $filter->filter();
        $this->assertSame('select * from "items"', $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_callback_filter_receives_value(): void
    {
        $strategy = new class () extends \Myerscode\Laravel\QueryStrategies\Strategies\Strategy {
            protected array $config = [];

            public function __construct()
            {
                $this->config = [
                    'min_score' => [
                        'callback' => static function ($builder, $value, $column): void {
                            $val = is_array($value) ? $value[0] : $value;
                            $builder->where('score', '>=', $val);
                        },
                    ],
                ];
                parent::__construct();
            }
        };

        $filter = new Filter(Item::query(), $strategy, ['min_score' => '42']);
        $filter->filter();
        $this->assertSame('select * from "items" where "score" >= \'42\'', $this->getRawSqlFromBuilder($filter->builder()));
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

    #[DataProvider('providerForDefaultValues')]
    public function test_default_filter_values(string $expectedSql, array $requestParams): void
    {
        $filter = $this->filter(Item::query(), new DefaultValueStrategy(), $requestParams);
        $filter->filter();
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($filter->builder()));
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

    #[DataProvider('providerForFields')]
    public function test_field_selection(string $expectedSql, string $strategyClass, array $requestParams): void
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        $filter = $this->filter(Item::query(), $strategy, $requestParams);
        $builder = $filter->fields()->builder();
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($builder));
    }

    public function test_fields_config_key_override(): void
    {
        $filter = $this->filter(Item::query(), new FieldSelectionStrategy(), ['fields' => 'secret', 'f' => 'id,name'], ['fields' => 'f']);
        $builder = $filter->fields()->builder();
        $this->assertSame('select "id", "name" from "items"', $this->getRawSqlFromBuilder($builder));
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

    #[DataProvider('providerForIgnoredValues')]
    public function test_ignored_filter_values(string $expectedSql, array $requestParams): void
    {
        $filter = $this->filter(Item::query(), new IgnoredValuesStrategy(), $requestParams);
        $filter->filter();
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($filter->builder()));
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

    #[DataProvider('providerForRelationshipFilter')]
    public function test_relationship_filtering(string $expectedSql, array $requestParams): void
    {
        $filter = $this->filter(Item::query(), new RelationshipFilterStrategy(), $requestParams);
        $filter->filter();
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($filter->builder()));
    }

    #[DataProvider('providerForScopeFilter')]
    public function test_scope_filtering(string $expectedSql, array $requestParams): void
    {
        $filter = $this->filter(Item::query(), new ScopeFilterStrategy(), $requestParams);
        $filter->filter();
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_strict_mode_allows_operator_overrides(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['foo--contains' => 'bar'], ['strict' => true]);
        $filter->filter();
        $this->assertStringContainsString('like', $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_strict_mode_allows_system_keys(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['order' => 'id', 'sort' => 'desc', 'limit' => '10', 'with' => 'owner', 'fields' => 'id', 'page' => '1'], ['strict' => true]);
        $filter->filter();
        $this->assertSame('select * from "items"', $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_strict_mode_allows_valid_filters(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['foo' => 'bar'], ['strict' => true]);
        $filter->filter();
        $this->assertSame('select * from "items" where "foo" = \'bar\'', $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_strict_mode_off_ignores_unknown(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['unknown' => 'value'], ['strict' => false]);
        $filter->filter();
        $this->assertSame('select * from "items"', $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_strict_mode_throws_for_unknown_filter(): void
    {
        $this->expectException(InvalidFilterException::class);
        $this->expectExceptionMessage("Filter 'unknown' is not allowed");

        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['unknown' => 'value'], ['strict' => true]);
        $filter->filter();
    }

    #[DataProvider('providerForTrashedFilter')]
    public function test_trashed_filtering(string $expectedSql, array $requestParams): void
    {
        $this->softDeletableDatabase($this->app);
        $filter = $this->filter(SoftDeletableItem::query(), new TrashedFilterStrategy(), $requestParams);
        $filter->filter();
        $this->assertSame($expectedSql, $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_triple_dash_parameter_is_ignored(): void
    {
        $strategy = $this->strategyManager()->findStrategy(ComplexConfigQueryStrategy::class);
        $filter = $this->filter(Item::query(), $strategy, ['foo--bar--baz' => 'value']);
        $filter->filter();

        $this->assertSame('select * from "items"', $this->getRawSqlFromBuilder($filter->builder()));
    }

    public function test_with_allows_all_when_canwith_empty(): void
    {
        $filter = $this->filter(Item::query(), new ComplexConfigQueryStrategy(), ['with' => 'owner,categories']);
        $builder = $filter->with()->builder();
        $this->assertSame(['owner', 'categories'], array_keys($builder->getEagerLoads()));
    }

    public function test_with_blocks_all_when_none_allowed(): void
    {
        $filter = $this->filter(Item::query(), new RestrictedWithStrategy(), ['with' => 'categories,secret']);
        $builder = $filter->with()->builder();
        $this->assertSame([], array_keys($builder->getEagerLoads()));
    }

    public function test_with_filters_to_allowed_relationships(): void
    {
        $filter = $this->filter(Item::query(), new RestrictedWithStrategy(), ['with' => 'owner,categories']);
        $builder = $filter->with()->builder();
        $this->assertSame(['owner'], array_keys($builder->getEagerLoads()));
    }
}
