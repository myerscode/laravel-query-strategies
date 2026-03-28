<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotSetException;
use Myerscode\Laravel\QueryStrategies\Filter;
use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotFoundException;
use Myerscode\Laravel\QueryStrategies\Facades\Query;
use Myerscode\Laravel\QueryStrategies\FilterBuilder;
use Myerscode\Laravel\QueryStrategies\StrategyManager;
use Tests\Support\Models\Item;
use Tests\Support\Models\Record;
use Tests\Support\Models\Register;
use Tests\Support\Models\TodoList;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;

use function Myerscode\Laravel\QueryStrategies\filter;

/**
 * @coversDefaultClass \Myerscode\Laravel\QueryStrategies\FilterBuilder
 */
final class FilterBuilderTest extends TestCase
{
    public function test_can_filter_using_model_config_for_strategy(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('records', static function (\Illuminate\Database\Schema\Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('foo')->nullable();
            $blueprint->string('bar')->nullable();
            $blueprint->timestamps();
        });

        $lengthAwarePaginator = filter(Record::class)->results();
        $this->assertInstanceOf(LengthAwarePaginator::class, $lengthAwarePaginator);
    }

    public function test_can_find_builder_from_builder(): void
    {
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder->filter(Item::query()));
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = Query::filter(Item::query());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = filter(Item::query());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());
    }

    public function test_can_find_builder_from_class(): void
    {
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder->filter(Item::class));
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = Query::filter(Item::class);
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = filter(Item::class);
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());
    }

    public function test_can_find_builder_from_model(): void
    {
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder->filter(new Item()));
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = Query::filter(new Item());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = filter(new Item());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());
    }

    public function test_filter_builder_instance_creation(): void
    {
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);

        $filterBuilder = $this->app->make(FilterBuilder::class);
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);

        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $this->assertInstanceOf(Filter::class, $filterBuilder->filter(Item::class)->with(ComplexConfigQueryStrategy::class));

        $filterBuilder = $this->app->make(FilterBuilder::class);
        $this->assertInstanceOf(Filter::class, $filterBuilder->filter(Item::class)->with(ComplexConfigQueryStrategy::class));
    }

    public function test_filter_with_returns_instance_of_filter(): void
    {
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $this->assertInstanceOf(Filter::class, $filterBuilder->filter(new Item())->with(ComplexConfigQueryStrategy::class));
    }

    public function test_filtering_with_invalid_builder_or_model_throws_builder_not_found_exception(): void
    {
        $this->expectException(BuilderNotFoundException::class);
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $filterBuilder->filter('foodbar');
    }

    public function test_filtering_without_builder_throws_builder_not_set_exception(): void
    {
        $this->expectException(BuilderNotSetException::class);
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $filterBuilder->with(ComplexConfigQueryStrategy::class);
    }

    public function test_is_filterable_trait(): void
    {
        $register = new Register();
        $this->assertInstanceOf(Filter::class, $register->filter());
    }

    public function test_is_filterable_trait_throws_exception_if_strategy_not_present(): void
    {
        $this->expectException(BuilderNotSetException::class);
        $todoList = new TodoList();
        $this->assertInstanceOf(Filter::class, $todoList->filter());
    }

    public function test_passing_empty_builder_throws_builder_not_set_exception(): void
    {
        $this->expectException(BuilderNotSetException::class);
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $filterBuilder->filter('');
    }

    public function test_results_picks_up_strategy_from_model(): void
    {
        $this->simpleDatabase($this->app);

        $model = new class () extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'items';

            public string $strategy = \Tests\Support\Strategies\BasicConfigQueryStrategy::class;
        };

        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $result = $filterBuilder->filter($model)->results();
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_results_throws_exception_when_no_strategy_provided(): void
    {
        $this->expectException(BuilderNotSetException::class);
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $filterBuilder->filter(Item::class)->results();
    }

    public function test_with_accepts_array_strategy(): void
    {
        $filterBuilder = new FilterBuilder(new Request(), new StrategyManager());
        $filter = $filterBuilder->filter(Item::class)->with(['foo', 'bar']);
        $this->assertInstanceOf(Filter::class, $filter);
    }
}
