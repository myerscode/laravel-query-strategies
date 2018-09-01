<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Myerscode\Laravel\QueryStrategies\Filter;
use Myerscode\Laravel\QueryStrategies\StrategyManager;
use Myerscode\Laravel\QueryStrategies\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class
        ];
    }

    public function simpleDatabase(Application $app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('likes');
            $table->timestamps();
        });
    }

    public function filter($builderOrModel, $strategyClass, $request = null, $config = []): Filter
    {
        $strategy = $this->strategyManager()->findStrategy($strategyClass);
        return new Filter($builderOrModel, $strategy, $request ?? [], $config);
    }

    public function request($replace = [])
    {
        $request = $this->app->make('Illuminate\Http\Request');
        $request->replace($replace);
        return $request;
    }

    public function strategyManager(): StrategyManager
    {
        return new StrategyManager;
    }

    public function getRawSqlFromBuilder(Builder $builder)
    {
        $query = str_replace(['?'], ['\'%s\''], $builder->toSql());
        $rawSql = vsprintf($query, $builder->getBindings());
        return $rawSql;
    }
}
