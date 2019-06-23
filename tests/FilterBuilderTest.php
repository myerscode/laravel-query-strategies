<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotSetException;
use Myerscode\Laravel\QueryStrategies\Filter;
use Myerscode\Laravel\QueryStrategies\Exceptions\BuilderNotFoundException;
use Myerscode\Laravel\QueryStrategies\Facades\Query;
use Myerscode\Laravel\QueryStrategies\FilterBuilder;
use Myerscode\Laravel\QueryStrategies\StrategyManager;
use Tests\Support\Models\Item;
use Tests\Support\Models\Register;
use Tests\Support\Models\TodoList;
use Tests\Support\Strategies\ComplexConfigQueryStrategy;

/**
 * @coversDefaultClass \Myerscode\Laravel\QueryStrategies\FilterBuilder
 */
class FilterBuilderTest extends TestCase
{

    public function testFilterBuilderInstanceCreation()
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

    public function testCanFindBuilderFromClass()
    {
        $filterBuilder = new FilterBuilder( new Request(), new StrategyManager());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder->filter(Item::class));
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = Query::filter(Item::class);
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = filter(Item::class);
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());
    }

    public function testCanFindBuilderFromModel()
    {
        $filterBuilder = new FilterBuilder( new Request(), new StrategyManager());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder->filter(new Item));
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = Query::filter(new Item);
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = filter(new Item);
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());
    }

    public function testCanFindBuilderFromBuilder()
    {
        $filterBuilder = new FilterBuilder( new Request(), new StrategyManager());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder->filter(Item::query()));
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = Query::filter(Item::query());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());

        $filterBuilder = filter(Item::query());
        $this->assertInstanceOf(FilterBuilder::class, $filterBuilder);
        $this->assertInstanceOf(Builder::class, $filterBuilder->builder());
    }

    public function testFilterWithReturnsInstanceOfFilter()
    {
        $filterBuilder = new FilterBuilder( new Request(), new StrategyManager());
        $this->assertInstanceOf(Filter::class, $filterBuilder->filter(new Item)->with(ComplexConfigQueryStrategy::class));
    }

    public function testFilteringWithInvalidBuilderOrModelThrowsBuilderNotFoundException()
    {
        $this->expectException(BuilderNotFoundException::class);
        $filterBuilder = new FilterBuilder( new Request(), new StrategyManager());
        $filterBuilder->filter('foodbar');
    }

    public function testFilteringWithoutBuilderThrowsBuilderNotSetException()
    {
        $this->expectException(BuilderNotSetException::class);
        $filterBuilder = new FilterBuilder( new Request(), new StrategyManager());
        $filterBuilder->with(ComplexConfigQueryStrategy::class);
    }

    public function testPassingEmptyBuilderThrowsBuilderNotSetException()
    {
        $this->expectException(BuilderNotSetException::class);
        $filterBuilder = new FilterBuilder( new Request(), new StrategyManager());
        $filterBuilder->filter('');
    }

    public function testIsFilterableTrait()
    {
        $model = new Register();
        $this->assertInstanceOf(Filter::class, $model->filter());
    }

    public function testIsFilterableTraitThrowsExceptionIfStrategyNotPresent()
    {
        $this->expectException(BuilderNotSetException::class);
        $model = new TodoList();
        $this->assertInstanceOf(Filter::class, $model->filter());
    }
}
